#include <Arduino_JSON.h>

#include  <ESP8266WiFi.h> 
#include  <ESP8266HTTPClient.h>
#include  <WiFiClientSecure.h> 

#define DEBUG 1

#define LEDPIN 2 
#define DOOR_CLOSED_PIN 1
#define MOTOR_CLOCKWISE 2
#define MOTOR_ANTICLOCKWISE 3

void doGet(WiFiClientSecure &httpsClient, int &status, String &bodyJSON);
void extractJson(String json, int &id, int &status, int &newStatus);
void doPost(WiFiClientSecure &httpsClient, String body, int &status);
bool reconnect(WiFiClientSecure &httpsClient);
void dump(String s, bool newLine);
void dump(String s);
void ICACHE_RAM_ATTR doorClosedCallback();
void ICACHE_RAM_ATTR doorOpenedCallback();

const char* fingerprint = "";

String deviceId = "1";
const int httpsPort = 443;

const char* ssid ="";
const char* password = "";
String serverName = "";
String getEndpoint = "/api.php?akcja=getDevice&id="+deviceId;
String postEndpoint = "/api.php";
String apiKey = "";

WiFiClient client;
volatile bool doorClosed = 0; 

void setup() {
  Serial.begin(115200) ;
  pinMode(DOOR_CLOSED_PIN, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(DOOR_CLOSED_PIN), doorClosedCallback, FALLING);
  // initialize GPIO 2 as an output.
  pinMode(LEDPIN, OUTPUT);
  
  dump(String("Connecting to %s") + ssid);
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    dump(".", false);
  }
   
  dump("WiFi connected");
  
}

// the loop function runs over and over again forever
void loop() {
  if(doorClosed){
    digitalWrite(LEDPIN, 1);
    dump("1");
  }
  else {
    dump("0");
    digitalWrite(LEDPIN, 0);
  }
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  WiFiClientSecure httpsClient;
  httpsClient.setFingerprint(fingerprint);
  dump("HTTPS Connecting"+serverName);
  
  reconnect(httpsClient);
  
  int httpsStatus, id, status, newStatus;
  String json;
  doGet(httpsClient, httpsStatus, json);
  dump("Status: "+httpsStatus);
  if(httpsStatus != 200){
    return;
  }
  
  extractJson(json, id, status, newStatus);
  dump("id: "+String(id));
  dump("status: "+status);
  dump("newStatus: "+newStatus);
  if(newStatus == status){
    return;
  }

  switch(newStatus) {
    case 1: 
      break;
    case 2:
      break;
  }
  
  String body = "id="+String(id)+"&akcja=updateStatus&status="+String(newStatus);
  dump("body: "+body);
  httpsStatus = 0;
  reconnect(httpsClient);
  doPost(httpsClient, body, httpsStatus);
  
  delay(1000);
//  digitalWrite(LEDPIN, HIGH);   // turn the LED on (HIGH is the voltage level)
//  delay(300);               // wait for a second
//  digitalWrite(LEDPIN, LOW);    // turn the LED off by making the voltage LOW
//  delay(300);               // wait for a second
}

void doGet(WiFiClientSecure &httpsClient, int &status, String &bodyJSON)
{
  String request = String("GET ") + getEndpoint + " HTTP/1.1\r\n" +
               "Host: " + serverName + "\r\n" +
               "x-api-key: " + apiKey + "\r\n" +
               "User-Agent: BuildFailureDetectorESP8266\r\n" + 
               "Connection: close\r\n\r\n";
  httpsClient.print(request);
  String response = httpsClient.readString();
  String data = "";
  char bodyBuffer[255];
  char rest[1023];
  
  int buffer_len = response.length() + 1;
  char buffer[buffer_len];
  response.toCharArray(buffer, buffer_len);
  sscanf(buffer, "%*s{%s}", bodyBuffer);
  sscanf(buffer, "HTTP/1.1 %d ",&status);

  bool inBrakets = false;
  for(int i=0;i<response.length();i++)
  {
    if(response[i] == '{')
      inBrakets = true;
    else if(response[i] == '}'){
      data += response[i];
      inBrakets = false;
      break;
    }
    if(inBrakets){
      data += response[i];
    }
  }
  bodyJSON = data;
}

void extractJson(String json, int &id, int &status, int &newStatus)
{
  dump(json);
  JSONVar jsonObj = JSON.parse(json);
  String str;
  str = jsonObj["id"];
  id = str.toInt();
  str = jsonObj["status"];
  status = str.toInt();
  str = jsonObj["new_status"];
  newStatus = str.toInt();
}

void doPost(WiFiClientSecure &httpsClient, String body, int &status)
{
  String request = String("POST ") + postEndpoint+ "?" + body + " HTTP/1.1\r\n" +
               "Host: " + serverName + "\r\n" +
               "x-api-key: " + apiKey + "\r\n" +
               "User-Agent: BuildFailureDetectorESP8266\r\n" + 
               "Connection: close\r\n\r\n";
  dump(request);
  
  httpsClient.print(request);
  String response = httpsClient.readString();
  dump(response);
  int buffer_len = response.length() + 1;
  char buffer[buffer_len];
  response.toCharArray(buffer, buffer_len);
  
  sscanf(buffer, "HTTP/1.1 %d ",&status);
  dump(String(status));
}

bool reconnect(WiFiClientSecure &httpsClient)
{
  int r=0; //retry counter
  while((!httpsClient.connect(serverName, httpsPort)) && (r < 30)){
      delay(100);
      dump(".", false);
      r++;
  }
  if(r==30) {
    dump("Connection failed");
    return false;
  }
  else {
    dump("Connected to web");
    return true;
  }
}

void dump(String s) {
  if(DEBUG) {
      Serial.println(s);
  }
}
void dump(String s, bool newLine) {
  if(DEBUG) {
    if(newLine)
      Serial.println(s);
    else
      Serial.print(s);
  }
}

void doorClosedCallback() {
  doorClosed = 1;
}
void doorOpenedCallback() {
  doorClosed = 0;
}
