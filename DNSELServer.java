
// Copyright (c) 2006-2007, Joseph B. Kowalski
// See LICENSE for licensing information 

import java.net.*;
import java.util.*;
import java.lang.*;
import java.util.Properties;
import java.io.*;
import java.lang.Math.*;
import java.sql.Connection;
import java.sql.Statement;
import java.sql.ResultSet;
import java.sql.PreparedStatement;
import java.sql.DriverManager;
import java.sql.SQLException;

 
class DNSELServer implements Runnable
{
	// Declare and initialize variables
	static String ListenIP;
	static int ListenPort;
	static String MatchReturnAddress;
	static String DNSEL_Domain;
	static String DNSEL_IP;
	static String SQL_Server;
	static int SQL_Port;
	static String SQL_User;
	static String SQL_Pass;
	static String SQL_Catalog;
	
	// DNS RCODE Constants
	static final int NOERROR = 0;
	static final int FORMERR = 1;
	static final int SERVFAIL = 2;
	static final int NXDOMAIN = 3;
	static final int NOTIMP = 4;
	static final int REFUSED = 5;
	static final int YXDOMAIN = 6;
	static final int YXRRSET = 7;
	static final int NXRRSET = 8;
	static final int NOTAUTH = 9;
	static final int NOTZONE = 10;
	static final int BADVERS = 16;
	
	// DNS OPCODE Constants
	static final int QUERY = 0;
	static final int IQUERY = 1;
	static final int STATUS = 2;
	static final int NOTIFY = 4;
	static final int UPDATE = 5;
	
	// DNS QCLASS/CLASS Constants
	static final int RESERVED0 = 0;
	static final int IN = 1;
	static final int CH = 3;
	static final int HS = 4;
	static final int NONE = 254;
	static final int ANY = 255;
	
	// DNS QTYPE/TYPE Constants
	static final int A = 1;

	// MASK_ARRAY Constants
	static final long[] MASK_ARRAY =
	{
		0x00000000,0x80000000,0xC0000000,0xE0000000,0xF0000000,0xF8000000,0xFC000000,0xFE000000,
		0xFF000000,0xFF800000,0xFFC00000,0xFFE00000,0xFFF00000,0xFFF80000,0xFFFC0000,0xFFFE0000,
		0xFFFF0000,0xFFFF8000,0xFFFFC000,0xFFFFE000,0xFFFFF000,0xFFFFF800,0xFFFFFC00,0xFFFFFE00,
		0xFFFFFF00,0xFFFFFF80,0xFFFFFFC0,0xFFFFFFE0,0xFFFFFFF0,0xFFFFFFF8,0xFFFFFFFE,0xFFFFFFFF
	};
	
	// DNS Header variables
	static int ID;
	static byte QR;
	static byte OPCODE;
	static byte AA;
	static byte TC;
	static byte RD;
	static byte RA;
	static byte Z;
	static byte RCODE;
	static int QDCOUNT;
	static int ANCOUNT;
	static int NSCOUNT;
	static int ARCOUNT; 
	
	// DNS Question variables
	static String QNAME = "";
	static int QTYPE = 0;
	static int QCLASS = 0;
	
	// DNS Answer variables
	static int NAME = 49164;
	static int TYPE = 1;
	static int CLASS = 1;
	static long TTL;
	static int RDLENGTH = 4;
	static InetAddress RDATA;   
	
	static byte[] ResponseBuffer;
	static byte[] QuestionArray;

	// DB Connection Items
	static boolean DBFailure = false;
	static Connection DBH = null;
	static PreparedStatement DBPS_IPMatchCount = null; 
	static PreparedStatement DBPS_ExitPolicy = null;
	static PreparedStatement DBPS_WriteLog = null;
	static ResultSet DBRS = null; 

	static boolean GeneralFailure = false;

	// Logging variables
	static int LOG_TOTAL = 0;
	static int LOG_NOERROR = 0;
	static int LOG_SERVFAIL = 0;
	static int LOG_NXDOMAIN = 0;
	static int LOG_NOTIMP = 0;

	
	public static void main( String args[] ) throws Exception
	{
		// Read in configuration settings
		Properties Config = new Properties();

		try
		{
			Config.load(new FileInputStream(new File("DNSELServer.properties")));
		}
		catch(IOException ie)
		{
			System.out.println("\nFailed to open configuration file!\n");
			System.exit(0);
		}

		try
		{
			ListenIP = Config.getProperty("config_ListenIP");
			ListenPort = Integer.parseInt(Config.getProperty("config_ListenPort"));
			TTL = Long.parseLong(Config.getProperty("config_TimeToLive"));
			MatchReturnAddress = Config.getProperty("config_MatchReturnAddress");
			DNSEL_Domain = Config.getProperty("config_DNSEL_Domain");
			DNSEL_IP = Config.getProperty("config_DNSEL_IP");
			SQL_Server = Config.getProperty("config_SQL_Server");
			SQL_Port = Integer.parseInt(Config.getProperty("config_SQL_Port"));
			SQL_User = Config.getProperty("config_SQL_User");
			SQL_Pass = Config.getProperty("config_SQL_Pass");
			SQL_Catalog = Config.getProperty("config_SQL_Catalog");
		}
		catch(NumberFormatException e)
		{
			System.out.println("\nFailed while reading configuration file!\n");
			System.exit(0);
		}		
                    
		// Declare necessary variables/objects
		boolean ProcessLoopControl = true;

		DatagramPacket ReqPacket;
		DatagramPacket RespPacket;
		
	       InetAddress RemoteAddress;
	       int RemotePort; 
		
		// Setup socket connection
		InetAddress ListenAddress = InetAddress.getByName(ListenIP);

		DatagramSocket DNSELSocket = new DatagramSocket(ListenPort,ListenAddress);

		// Setup database connection
		try
		{
			Class.forName("com.mysql.jdbc.Driver").newInstance();
		}
		catch (Exception ex)
		{
			System.out.println("\nFailure registering database driver!\n");
			System.exit(0);
		}

		try
		{
			DBH = DriverManager.getConnection("jdbc:mysql://" + SQL_Server + ":" + SQL_Port + "/" + SQL_Catalog + "?user=" + SQL_User + "&password=" + SQL_Pass);
			DBPS_IPMatchCount = DBH.prepareStatement("select count(*) as Count from DNSEL where IP = ?");
			DBPS_ExitPolicy = DBH.prepareStatement("select ExitPolicy from DNSEL where IP = ?");
			DBPS_WriteLog = DBH.prepareStatement("insert into DNSEL_LOG (Timestamp,TotalResponses,NOERROR,SERVFAIL,NXDOMAIN,NOTIMP) values (now(),?,?,?,?,?)");
		}
		catch (SQLException ex)
		{
			System.out.println("\nFailure establishing database connection!\n");
			System.exit(0);
		}

		// Start logging thread
		(new Thread(new DNSELServer())).start();
		
		while(ProcessLoopControl)
		{         
			ReqPacket = new DatagramPacket(new byte[1468],1468);
			DNSELSocket.receive(ReqPacket);
			RemoteAddress = ReqPacket.getAddress();
			RemotePort = ReqPacket.getPort();
			  
			// Populate static HEADER and QUESTION variables from received packet 
			processReceivedPacket(ReqPacket.getData());
			
			if ((QTYPE == A || QTYPE == ANY) && (DNSEL_Domain.equals(QNAME)))
			{
				QR = 1;
				AA = 1;
				RD = 0;
				RA = 0;
				RCODE = NOERROR;
				QDCOUNT = 1;
				ANCOUNT = 1;
				RDATA = InetAddress.getByName(DNSEL_IP);

				LOG_NOERROR++;
				LOG_TOTAL++;
			}
			else if ((QTYPE == A || QTYPE == ANY) && (QNAME.indexOf(DNSEL_Domain) > -1))
			{
				if (DNSEL_Lookup())
				{
					QR = 1;
					AA = 1;
					RD = 0;
					RA = 0;
					RCODE = NOERROR;
					QDCOUNT = 1;
					ANCOUNT = 1;
					RDATA = InetAddress.getByName(MatchReturnAddress);

					LOG_NOERROR++;
					LOG_TOTAL++;
				}
				else
				{
					if(DBFailure || GeneralFailure)
					{
						if(DBFailure)
						{
							DBFailure = false;

							// Attempt to re-establish DB Connection
							try
							{
								if(DBH != null)
								{
									try
									{

										if(DBRS != null)
										{
											try
											{
												DBRS.close();
												DBRS = null;
											}
											catch(SQLException ex)
											{}
										}

										if (DBPS_IPMatchCount != null)
										{
											try
											{
												DBPS_IPMatchCount.close();
												DBPS_IPMatchCount = null;
											}
											catch(SQLException ex)
											{}
										}										

										if(DBPS_ExitPolicy != null)
										{
											try
											{
												DBPS_ExitPolicy.close();
												DBPS_ExitPolicy = null;
											}
											catch(SQLException ex)
											{}
										}

										if(DBPS_WriteLog != null)
										{
											try
											{
												DBPS_WriteLog.close();
												DBPS_WriteLog = null;
											}
											catch(SQLException ex)
											{}
										}

										DBH.close();
										DBH = null;
									}
									catch(SQLException ex)
									{}
								}

								DBH = DriverManager.getConnection("jdbc:mysql://" + SQL_Server + ":" + SQL_Port + "/" + SQL_Catalog + "?user=" + SQL_User + "&password=" + SQL_Pass);
								DBPS_IPMatchCount = DBH.prepareStatement("select count(*) as Count from DNSEL where IP = ?");
								DBPS_ExitPolicy = DBH.prepareStatement("select ExitPolicy from DNSEL where IP = ?");
								DBPS_WriteLog = DBH.prepareStatement("insert into DNSEL_LOG (Timestamp,TotalResponses,NOERROR,SERVFAIL,NXDOMAIN,NOTIMP) values (now(),?,?,?,?,?)");
							}
							catch (SQLException ex)
							{}
						}

						if(GeneralFailure)
						{
							GeneralFailure = false;
						}

						QR = 1;
						AA = 1;
						RD = 0;
						RA = 0;
						RCODE = SERVFAIL;
						QDCOUNT = 1;
						ANCOUNT = 0;

						LOG_SERVFAIL++;
						LOG_TOTAL++;
					}
					else
					{
						QR = 1;
						AA = 1;
						RD = 0;
						RA = 0;
						RCODE = NXDOMAIN;
						QDCOUNT = 1;
						ANCOUNT = 0;

						LOG_NXDOMAIN++;
						LOG_TOTAL++;
					}
				}
			}
			else if ((QTYPE != A && QTYPE != ANY) && (QNAME.indexOf(DNSEL_Domain) > -1))
			{
				QR = 1;
				AA = 1;
				RD = 0;
				RA = 0;
				RCODE = NXDOMAIN;
				QDCOUNT = 1;
				ANCOUNT = 0;

				LOG_NXDOMAIN++;
				LOG_TOTAL++;
			}
			else if (QNAME.indexOf(DNSEL_Domain) == -1)
			{
				QR = 1;
				AA = 0;
				RD = 0;
				RA = 0;
				RCODE = SERVFAIL;
				QDCOUNT = 1;
				ANCOUNT = 0;

				LOG_SERVFAIL++;
				LOG_TOTAL++;
			}
			else
			{
				QR = 1;
				AA = 0;
				RD = 0;
				RA = 0;
				RCODE = SERVFAIL;
				QDCOUNT = 1;
				ANCOUNT = 0;

				LOG_SERVFAIL++;
				LOG_TOTAL++;
			}
			
			// Generate response
			generateResponsePacket();
			
		 	// Send response
		 	RespPacket = new DatagramPacket(ResponseBuffer, ResponseBuffer.length, RemoteAddress, RemotePort);
			DNSELSocket.send(RespPacket);
		}
	}


	public static long GetBitField(long Input, byte StartBit, byte EndBit)
	{
		long Result = 0;
		for(byte BitCount = StartBit ; BitCount <= EndBit ; BitCount++)
		{
			long LoweredValue = Input & (long)Math.pow(2,BitCount);
			if(LoweredValue > 0)
			{
				for(byte Decrementer = StartBit ; Decrementer > 0 ; Decrementer--)
				{
					LoweredValue /= 2;
				}
				
				Result += LoweredValue;
			}
		}
	
		return Result;
	}


	public static final int unsignedShortToInt(byte[] b) 
	{
	    int i = 0;
	    i |= b[0] & 0xFF;
	    i <<= 8;
	    i |= b[1] & 0xFF;
	    return i;
	}
	 
	
	public static final int unsignedByteToInt(byte b) 
	{
	    int i = 0;
	    i |= b & 0xFF;
	    return i;
	}
	    
	
	public static final void processReceivedPacket(byte[] RequestBuffer)
	{
		byte[] IDArray = {RequestBuffer[0],RequestBuffer[1]};
		ID = unsignedShortToInt(IDArray);

		byte[] FlagsArray = {RequestBuffer[2],RequestBuffer[3]};
		int Flags = unsignedShortToInt(FlagsArray);

		QR = (byte)GetBitField(Flags,(byte)15,(byte)15);
		OPCODE = (byte)GetBitField(Flags,(byte)11,(byte)14);
		AA = (byte)GetBitField(Flags,(byte)10,(byte)10);
		TC = (byte)GetBitField(Flags,(byte)9,(byte)9);
		RD = (byte)GetBitField(Flags,(byte)8,(byte)8);
		RA = (byte)GetBitField(Flags,(byte)7,(byte)7);
		Z = (byte)GetBitField(Flags,(byte)4,(byte)6);
		RCODE = (byte)GetBitField(Flags,(byte)0,(byte)3);

		byte[] QDCOUNTArray = {RequestBuffer[4],RequestBuffer[5]};
		QDCOUNT = unsignedShortToInt(QDCOUNTArray);

		byte[] ANCOUNTArray = {RequestBuffer[6],RequestBuffer[7]};
		ANCOUNT = unsignedShortToInt(ANCOUNTArray);

		byte[] NSCOUNTArray = {RequestBuffer[8],RequestBuffer[9]};
		NSCOUNT = unsignedShortToInt(NSCOUNTArray);

		byte[] ARCOUNTArray = {RequestBuffer[10],RequestBuffer[11]};
		ARCOUNT = unsignedShortToInt(ARCOUNTArray);
                                                                    
		if (RequestBuffer[12] != 0)
		{
			QNAME = "";
				
			int ByteCounter = 12;
				
			while (RequestBuffer[ByteCounter] != 0)
			{
				int ChunkSize = unsignedByteToInt(RequestBuffer[ByteCounter]);
				
				ByteCounter++;
				
				for (int x = 0 ; x < ChunkSize ; x++)
				{
				
					QNAME += (char)RequestBuffer[ByteCounter];
					ByteCounter++;
				}
				
				if (RequestBuffer[ByteCounter] != 0)
				{
					QNAME += '.';
				}
			}
			
			byte [] QTYPEArray = {RequestBuffer[ByteCounter + 1],RequestBuffer[ByteCounter + 2]};
			QTYPE = unsignedShortToInt(QTYPEArray);
			
			byte [] QCLASSArray = {RequestBuffer[ByteCounter + 3],RequestBuffer[ByteCounter + 4]};
			QCLASS = unsignedShortToInt(QCLASSArray);
			
			QuestionArray = new byte[(ByteCounter + 5)-12];
			int QuestionArrayCount = 0;
			
			for (int i = 12 ; i < (ByteCounter + 5) ; i++)
			{
				QuestionArray[QuestionArrayCount] = RequestBuffer[i];
				QuestionArrayCount++;
			}
			
			QNAME = QNAME.toLowerCase();
		}
	} 
	
	
	public static final void generateResponsePacket()
	{          
		byte[] LocalResponseBuffer = new byte[1468];
		
		LocalResponseBuffer[0] = (byte)((ID & 0x0000FF00) / 256);
		LocalResponseBuffer[1] = (byte)(ID & 0x000000FF);
				
		int FLAGS = RCODE;
		FLAGS += Z * 16;
		FLAGS += RA * 128;
		FLAGS += RD * 256;
		FLAGS += TC * 512;
		FLAGS += AA * 1024;
		FLAGS += OPCODE * 2048;
		FLAGS += QR * 32768;
		
		LocalResponseBuffer[2] = (byte)((FLAGS & 0x0000FF00) / 256);
		LocalResponseBuffer[3] = (byte)(FLAGS & 0x000000FF);
		
		LocalResponseBuffer[4] = (byte)((QDCOUNT & 0x0000FF00) / 256);
		LocalResponseBuffer[5] = (byte)(QDCOUNT & 0x000000FF);
		
		LocalResponseBuffer[6] = (byte)((ANCOUNT & 0x0000FF00) / 256);
		LocalResponseBuffer[7] = (byte)(ANCOUNT & 0x000000FF);
		
		LocalResponseBuffer[8] = (byte)((NSCOUNT & 0x0000FF00) / 256);
		LocalResponseBuffer[9] = (byte)(NSCOUNT & 0x000000FF);
		
		LocalResponseBuffer[10] = (byte)((ARCOUNT & 0x0000FF00) / 256);
		LocalResponseBuffer[11] = (byte)(ARCOUNT & 0x000000FF);
		
		int ResponseBufferIndex = 12;
		
		for (int i = 0 ; i < QuestionArray.length ; i++)
		{
			LocalResponseBuffer[ResponseBufferIndex] = QuestionArray[i];
			ResponseBufferIndex++;
		}

		if (ANCOUNT == 1)
		{
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((NAME & 0x0000FF00) / 256);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)(NAME & 0x000000FF);
			
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((TYPE & 0x0000FF00) / 256);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)(TYPE & 0x000000FF);
			
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((CLASS & 0x0000FF00) / 256);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)(CLASS & 0x000000FF);
                	
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((TTL & 0xFF000000) / 16777216);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((TTL & 0x00FF0000) / 65536);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((TTL & 0x0000FF00) / 256);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)(TTL & 0x000000FF);
			
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)((RDLENGTH & 0x0000FF00) / 256);
			ResponseBufferIndex++;
			LocalResponseBuffer[ResponseBufferIndex] = (byte)(RDLENGTH & 0x000000FF);
			      
			byte[] ReturnIP = RDATA.getAddress();
			
			for(int i = 0 ; i < ReturnIP.length ; i++)
			{
				ResponseBufferIndex++;
				LocalResponseBuffer[ResponseBufferIndex] = ReturnIP[i];
			}
			
			ResponseBufferIndex++;  
		}
		
		ResponseBuffer = new byte[ResponseBufferIndex];
		for (int i = 0; i < ResponseBufferIndex ; i++)
		{
			ResponseBuffer[i] = LocalResponseBuffer[i];
		}
	}
	
	
	public static final boolean DNSEL_Lookup()
	{
		try
		{
			// Declare and initialize variables
			String[] RequestArray;
			String QueryIP;
			String DestinationIP;
			int DestinationPort;
			int QueryIPDBCount; 
			String[] TorNodeExitPolicy;

			// Remove DNSEL domain name (And leading .) from QNAME
			QNAME = QNAME.replace("." + DNSEL_Domain,"");

			// Split up QNAME by "."
			RequestArray = QNAME.split("\\.");

			// Ensure there are the correct number of pieces in the array (9)
			if(RequestArray.length != 9)
			{
				return false;
			}

			// Populate pieces of the array into their correct variables
			QueryIP = RequestArray[3] + "." + RequestArray[2] + "." + RequestArray[1] + "." + RequestArray[0];
			DestinationIP = RequestArray[8] + "." + RequestArray[7] + "." + RequestArray[6] + "." + RequestArray[5];
			
			try
			{
				DestinationPort = Integer.parseInt(RequestArray[4]);
			}
			catch(NumberFormatException e)
			{
				return false;
			}

			// Various validation
			if(QueryIP.length() > 15 || DestinationIP.length() > 15)
			{
				return false;
			}

			try
			{
				InetAddress.getByName(QueryIP);
				InetAddress.getByName(DestinationIP);
			}
			catch(Exception ex)
			{
				return false;
			}

			if(DestinationPort < 0 || DestinationPort > 65535)
			{
				return false;
			}
		
			// Determine if query IP exists in database as a Tor server
			DBPS_IPMatchCount.setString(1, QueryIP);
			DBRS = DBPS_IPMatchCount.executeQuery();
			DBRS.next();
			QueryIPDBCount = DBRS.getInt(1);

			if (QueryIPDBCount < 1)
			{
				return false;
			}

			// Get exit policies of matching Tor servers
			DBPS_ExitPolicy.setString(1, QueryIP);
			DBRS = DBPS_ExitPolicy.executeQuery();
			
			while (DBRS.next())
			{
				TorNodeExitPolicy = DBRS.getString(1).split("\\:\\:");

				outer:
				for (int i=0 ; i < TorNodeExitPolicy.length ; i++)
				{
					// Initialize variables
					String ExitPolicyLine = TorNodeExitPolicy[i];
					String Condition;
					String NetworkLine;
					String Subnet;
					String PortLine;
					String[] Port = null;
					String[] Temp;

					// Separate parts of ExitPolicy line
					Temp = ExitPolicyLine.split("\\ ");
					Condition = Temp[0];
					NetworkLine = Temp[1];

					Temp = NetworkLine.split("\\:");
					Subnet = Temp[0];
					PortLine = Temp[1];

					Port = PortLine.split("\\,");

					// Find out if DestinationIP user provided is a match for the subnet specified on this ExitPolicy line
					if(IsIPInSubnet(DestinationIP,Subnet))
					{
						// Determine if port is also a match
						inner:
						for (int j=0 ; j < Port.length ; j++)
						{
							String CurrentPortExpression = Port[j];

							// Handle condition where port is a '*' character (Port always matches)
							if(CurrentPortExpression.equals("*"))
							{
								if(Condition.equals("accept"))
								{
									return true;
								}
								else if(Condition.equals("reject"))
								{
									break outer;
								}
							}

							// Handle condition where CurrentPortExpression is a range of ports
							if(CurrentPortExpression.indexOf("-") > -1)
							{
								Temp = CurrentPortExpression.split("\\-");
								int LowerPort;
								int UpperPort;

								try
								{
									LowerPort = Integer.parseInt(Temp[0]);
									UpperPort = Integer.parseInt(Temp[1]);
								}
								catch(NumberFormatException e)
								{
									return false;
								}

								if ((DestinationPort >= LowerPort) && (DestinationPort <= UpperPort) && (Condition.equals("accept")))
								{
									return true;
								}
								else if ((DestinationPort >= LowerPort) && (DestinationPort <= UpperPort) && (Condition.equals("reject")))
								{
									break outer;
								}
								else
								{
									continue;
								}
							}

							// Handle condition where CurrentPortExpression is a single port number
							else
							{
								int SinglePort;
								
								try
								{
									SinglePort = Integer.parseInt(CurrentPortExpression);
								}
								catch(NumberFormatException e)
								{
									return false;
								}

								if((DestinationPort == SinglePort) && (Condition.equals("accept")))
								{
									return true;
								}
								else if ((DestinationPort == SinglePort) && (Condition.equals("reject")))
								{
									break outer;
								}
								else
								{
									continue;
								}
							}
						}	
					}
					else
					{
						continue;
					}
				}
			}
			// Return false in situations where all matching Tor server exit policies have been evaluated, but no positive match could be found for DestinationPort & IP
			return false;
		}
		catch(SQLException ex)
		{
			DBFailure = true;
			return false;
		}
		finally
		{
 			if (DBRS != null)
			{
				try
				{
          				DBRS.close();
        			}
				catch (SQLException sqlEx)
				{}

				DBRS = null;
    			}
		}
	}


	public static final boolean IsIPInSubnet(String IP, String Subnet)
	{
		// Always return true if subnet is wildcard
		if(Subnet.equals("*"))
		{
			return true;
		}

		// Always return true if subnet and IP are exact match
		if(Subnet.equals(IP))
		{
			return true;
		}

		// Always return false if only an IP was provided, and it didn't match the above check
		if(Subnet.indexOf("/") == -1)
		{
			return false;
		}

		// Separate network address and mask bits
		String[] Temp;
		int Mask;

		Temp = Subnet.split("\\/");
		Subnet = Temp[0];
		
		try
		{
			Mask = Integer.parseInt(Temp[1]);
		}
		catch(NumberFormatException e)
		{
			GeneralFailure = true;
			return false;
		}
		
		// Convert IP to long
		long IP_Long;
	
		Temp = IP.split("\\.");
		try
		{
			IP_Long = Integer.parseInt(Temp[3]);
			IP_Long += (Integer.parseInt(Temp[2]) * 256);
			IP_Long += (Integer.parseInt(Temp[1]) * 65536);
			IP_Long += (Integer.parseInt(Temp[0]) * 16777216);
		}
		catch(NumberFormatException e)
		{
			GeneralFailure = true;
			return false;
		}

		// Convert network address to long
		long NetworkAddress_Long;
	
		Temp = Subnet.split("\\.");
		try
		{
			NetworkAddress_Long = Integer.parseInt(Temp[3]);
			NetworkAddress_Long += (Integer.parseInt(Temp[2]) * 256);
			NetworkAddress_Long += (Integer.parseInt(Temp[1]) * 65536);
			NetworkAddress_Long += (Integer.parseInt(Temp[0]) * 16777216);
		}
		catch(NumberFormatException e)
		{
			GeneralFailure = true;
			return false;
		}

		// Apply bit mask to IP and NetworkAddress
		IP_Long = IP_Long & MASK_ARRAY[Mask];
		NetworkAddress_Long = NetworkAddress_Long & MASK_ARRAY[Mask];

		// Return
		if (IP_Long == NetworkAddress_Long)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	// Method for logging thread
	public void run()
	{
		while(true)
		{
			try
			{
				Thread.sleep(300000);

				try
				{
					DBPS_WriteLog.setInt(1, LOG_TOTAL);
					DBPS_WriteLog.setInt(2, LOG_NOERROR);
					DBPS_WriteLog.setInt(3, LOG_SERVFAIL);
					DBPS_WriteLog.setInt(4, LOG_NXDOMAIN);
					DBPS_WriteLog.setInt(5, LOG_NOTIMP);
		
					DBPS_WriteLog.executeUpdate();
				}
				catch(SQLException ex)
				{}
			}
			catch(InterruptedException e)
			{}
		}
	}
}
