const express = require('express');
const https = require('https');
const fs = require('fs');
const path = require('path');
const util = require('util');
const os = require('os');

const USE_LOCALHOST = true;
const HTTP_PORT = 3000;
const HTTPS_PORT = 3001;
const KEY_PATH = 'certs/key.pem';
const CERT_PATH = 'certs/cert.pem';

const LOCALHOST_IP = '127.0.0.1';
const ROOT_DIR = path.resolve(process.argv[2] || './');

const app = express();
const ip_addresses = getAddresses();

if (!ip_addresses || ip_addresses.length < 1) {
  console.log("Could not resolve local IP address.");
  return 0;
}

var NODE_HOST = USE_LOCALHOST ? LOCALHOST_IP : ip_addresses[ip_addresses.length-1];

console.log('\n---------------');
console.log('Starting static hosts with root: ' + ROOT_DIR);
console.log('Press Ctrl + C to exit.');
console.log('---------------');

app.use(express.static(ROOT_DIR));

app.listen(HTTP_PORT, (err) => {
  if (err) {
    console.log('Http method retrurned an error when trying to start.', err);
  } else {
    console.log('Listening on: http://' + NODE_HOST + ':' + HTTP_PORT + '');
  }
});

const server = https.createServer({
  key: fs.readFileSync(path.resolve(__dirname, KEY_PATH)),
  cert: fs.readFileSync(path.resolve(__dirname, CERT_PATH))
}, app);
server.listen(HTTPS_PORT, NODE_HOST, (err) => {
  if (err) {
    console.log('Https method retrurned an error when trying to start.', err);
  } else {
    console.log('Listening on: https://' + NODE_HOST + ':' + HTTPS_PORT + '');
  }
});

//=======
function getAddresses() {
  var
    network = os.networkInterfaces(),
    interfaces = [],
    addresses = [];

  for (prop in network) {
    interfaces.push(network[prop]);
  }

  interfaces.forEach(function(net) {
    net.forEach(function(address) {
      if (address.family == 'IPv4' && !address.internal) addresses.push(address.address);
    });
  });

  return addresses;
}
