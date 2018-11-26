# super-simple-web-server
Serve static files on localhost with http & https.


## TO INSTALL

`npm install super-simple-web-server`

### or

Clone repo and run `npm install` in the root directory.


## TO RUN

`npm start [</path/to/web/root>]`

###Default path 
Default path is your current working directory.  Override by passing an optional path to your desired web root directory.

###Default ports
Default ports are `3000` (http) and `3001` (https).  These can be changed in `index.js`.

###Default IP
The default IP `127.0.0.1` which should convieniently map to `localhost`.

Pro tip: Setting `USE_LOCALHOST = false` in `index.js` will instead scan for existing bound IP addresses on your machine via `os.networkInterfaces()`.  The last available will be used.


## About the self-signed certifictes

Fictious self-signed certs are provided for your development convienience. They will exprire on June 6 2028. Obviously -- you don't want to use these for anything other than private testing.

See `./certs/` for more info...

