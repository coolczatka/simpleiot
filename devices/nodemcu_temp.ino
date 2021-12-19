#include <Arduino_JSON.h>

#include  <ESP8266WiFi.h> 
#include  <ESP8266HTTPClient.h>
#include  <WiFiClientSecure.h> 
#include <OneWire.h>
#include <DallasTemperature.h>

#define DEBUG 0

#define LEDPIN 2 
#define ONE_WIRE_BUS 14

void doGet(WiFiClientSecure &httpsClient, int &status, String &bodyJSON);
void extractJson(String json, int &id, int &status, int &newStatus);
void doPost(WiFiClientSecure &httpsClient, String body, int &status);
bool reconnect(WiFiClientSecure &httpsClient);
void dump(String s, bool newLine);
void dump(String s);
void blink(int delayy);

OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

const char* fingerprint = "54 AD C5 08 48 30 1A 85 85 71 B7 BB 89 5D BE B5 E1 F6 01 E2";

String deviceId = "2";
const int httpsPort = 443;

const char* ssid ="siądź pod mym liściem"; // Tu wpisz nazwę swojego wifi
const char* password = "Iluvatar"; // Tu wpisz hasło do swojego wifi
String serverName = "kurdeehalincia.pl";
String getEndpoint = "/bot/api.php?akcja=getDevice&id="+deviceId;
String postEndpoint = "/bot/api.php";
String apiKey = "moRxB6wJZJM6JOpVvrwc";

WiFiClient client;
volatile bool doorClosed = 0; 

void setup() {
  digitalWrite(LEDPIN, HIGH);
  if(DEBUG)
    Serial.begin(115200);
  sensors.begin();
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
  digitalWrite(LEDPIN, HIGH);
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
    blink(100);
    return;
  }
  sensors.requestTemperatures(); // Send the command to get temperatures
  Serial.println("DONE");
  float tempC = sensors.getTempCByIndex(0);

  String body = "id="+String(id)+"&akcja=sendValue&value="+String(tempC);
  dump("body: "+body);
  httpsStatus = 0;
  reconnect(httpsClient);
  doPost(httpsClient, body, httpsStatus);
  blink(500);
 
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
void blink(int delayy){
    digitalWrite(LEDPIN, LOW);   // turn the LED on (HIGH is the voltage level)
    delay(delayy);               // wait for a second
    digitalWrite(LEDPIN, HIGH );    // turn the LED off by making the voltage LOW
    delay(delayy);               // wait for a second
}
