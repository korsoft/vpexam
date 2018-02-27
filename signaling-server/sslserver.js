//require our websocket library 
var WebSocketServer = require('ws').Server,
	MySQL           = require('mysql'),
	FileSystem      = require('fs'),
	//Express         = require('express'),
	Https           = require('https');

process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
//in case of http request just send back "OK"
var processRequest = function(req, res) {
   res.writeHead(200);
   res.end('OK');
};

var httpsServer = Https.createServer({
		key  : FileSystem.readFileSync('/opt/local/apache2/ssl/localhost.key'), 
		cert : FileSystem.readFileSync('/opt/local/apache2/ssl/localhost.crt')
	}, processRequest).listen(8443);

//creating a websocket server at port 9090 
var	wss    = new WebSocketServer({
		server : httpsServer
	}),
//all connected to the server users
	users  = [],
//all connected to the server users
	usersPhysician  = [],                
//Path to the uploaded files
	path   = '/var/www/.uploads/';
//Connection to the db
var con = MySQL.createConnection({
  host     : '127.0.0.1',
  database : 'virtual_physical_secure',
  user     : 'vp_user',
  password : 'Vp-P4$s',
  multipleStatements : true
});
//Connect to MySQL
con.connect(function(err) {
  if(err) {
    console.log('Error connecting to DB. ', err);
    return;
  }
  console.log('MySQL Connection established.');
});
//when a user connects to our sever
wss.on('connection', function(connection) {
   //when server gets a message from a connected user 
   connection.on('message', function(message) {
   		if(undefined !== message) {
	   		if(message instanceof Buffer && undefined !== connection.id) {
	   			if(!Array.isArray(users[connection.id]['chunks'])) {
	   				users[connection.id]['chunks'] = [];
	   			}
				users[connection.id]['chunks'].push(message);
	   		}
	   		else {
			   	var data; 
			   	//accepting only JSON messages 
			   	try { 
			      	data = JSON.parse(message); 
			   	} catch (e) {
			      	console.log('Invalid JSON, e { ', e, ' }.'); 
			      	data = {}; 
			   	}
			   	//switching type of the user message 
			   	switch (data.type) { 
			      	case 'login' :
			         	//if anyone is logged in with this username then refuse 
			         	if(users[data.id]) { 
			         		console.log('User already logged { ', data.id, ' }.');
			            	sendTo(connection, {
			               		type    : 'login',
			               		success : false 
		            		}); 
			         	}
			         	else { 
			         		console.log('User logged { ', data.id, ' }.');
                                                //save user connection on the server  
                                                connection.id     = data.id;
                                                connection.status = 'available';
                                                users[data.id]    = connection;
                                                sendTo(connection, { 
                                                        type    : 'login',
                                                        success : true 
                                                });	
			         	}
						break;
					case 'offer' :
						//for ex. UserA wants to call UserB 
						console.log('Sending offer from { ', data.from.id, ' } to { ', data.to, ' }');
						//Get the user to send the offer
						var user = users[data.to];
						//If not exists
						if(null == user) {
							console.log('User { ', data.to, ' } is not connected');
							sendTo(connection, { 
								type    : 'offer',
								success : false
							});
						}
						//Si no es nulo pero esta ocupado
						else if('busy' == user.status) {
							console.log('User { ', data.to, ' } is on a call');
							sendTo(connection, { 
								type    : 'offer',
								success : false,
								status  : user.status
							});
						}
						//Sino es nulo y etsa disponible
						else {
							setBusy(data.from.id);
							sendTo(user, {
								type    : 'offer', 
								offer   : data.offer,
								success : true, 
								user    : data.from,
								record  : data.record
							});
						}
						break;		 
					case 'answer' :
						console.log('Sending answer from { ', data.from, ' } to { ', data.to, ' } '); 
						var conn = users[data.to];
						if(conn != null) {
							conn.otherid   = data.from;
							users[data.to] = conn;
							sendTo(conn, { 
								type   : 'answer', 
								answer : data.answer,
								record : data.record
							});
							var conn = users[data.from];
							if(conn != null) {
								conn.otherid   = data.to;
								users[data.from] = conn;
								setBusy(data.from);
							}
						}
						break;
					case 'candidate' :
						console.log('Sending candidate to: ', data.id); 
						var conn = users[data.id]; 
						if(null != conn) {
							sendTo(conn, { 
								type      : 'candidate', 
								candidate : data.candidate 
							}); 
						}
						break;
					case 'leave' :
						console.log('Disconnecting from: ', data.id);
						//notify the other user so he can disconnect his peer connection 
						var conn = users[data.id];
						if(null != conn) {
							otherid = conn.otherid;
							delete users[data.id];
							conn = users[otherid];
							if(null != conn) {
								/*
								conn.otherid   = null;
								conn.status    = 'available';
								users[data.id] = conn;
								console.log('Set available the user ', conn.id);
								*/
								sendTo(conn, { type : 'leave' });
							}
						}
						break;
					/*case 'record' :
						//Get the user
						var user = users[data.id]; 
						//If the user exists
						if(user != null) {
							con.query('SET @id = 0; CALL dbcode.sp_add_patient_video_call(@id, ' + data.otherid + ', ' + user.id + '); SELECT @id AS id', function(err, rows) {
							    if(err) {
							    	console.log('Error execute query. ', err);
							    	return;
							    }
							    console.log('Video id { ', rows[2][0].id, ' }');
							    var chunks = users[data.id]['chunks'];
							    console.log('Total of array buffers for user ', data.id, ', ', chunks.length);
							    if(0 < chunks.length) {
							    	var calls_path = path + data.otherid + '/' + user.id + '/calls/';
							    	if (!FileSystem.existsSync(calls_path)) {
									    mkdir(calls_path);
									}
							    	var video = FileSystem.createWriteStream(calls_path + rows[2][0].id + '.webm');
							    	for(var i = 0; i < chunks.length; i++) {
							    		video.write(toBuffer(chunks[i]));
							    	}
							    	console.log('Video created successfully!');
							    	video.end();
								}
							  }
							);
						}
						break;*/
                                        case 'check_status':
                                            console.log('Physician id { ', data.id, ' }');
                                            for (var i in usersPhysician) {//Cicle to get patients-physician array.
                                               if(data.id==usersPhysician[i].physicianid){//if physician has patients waiting (patients id in array)
                                                    var userPatient = users[usersPhysician[i].patientid];//get patient element from array
                                                    if(null != userPatient) {//If patient is online, send message
                                                        console.log('User { ',usersPhysician[i].patientid, ' } is  connected');
                                                        sendTo(userPatient, {
                                                            type    : 'check', 
                                                            success : true
                                                        });                             
                                                    }
                                                }
                                            }
                                        break;
                                        case 'check' : //Register patient and his physician id in array.
                                            if(typeof usersPhysician.find(function(item) {
                                              return ((typeof item != 'undefined'  )?item.patientid:'') == data.patientid;
                                              })== 'undefined')//If user is not in patients array.
                                            {
                                                usersPhysician.push({
                                                    physicianid: data.physicianid, 
                                                    patientid:  data.patientid
                                                });
                                                var userPatient = users[data.patientid];//get patient element from array
                                                var userPhysician = users[data.physicianid];//get physician element from array
                                                if(null != userPatient) {//If patient is online, send message
                                                    sendTo(userPatient, {
                                                        type    : 'check', 
                                                        success :  true
                                                    });        
                                                }
                                                if(null != userPhysician) {//If physician is online, send message
                                                    sendTo(userPhysician, {
                                                        type    : 'patientlogged', 
                                                        patientid:  data.patientid,
                                                        success : true
                                                    });
                                                }
                                            }                                              
                                        break;
			      	default :
			         	sendTo(connection, { 
			            	type    : 'error', 
			           	 	message : 'Command no found: ' + data.type 
			        	 }); 
			         	break; 
			   }         
			}
		}
	});
   	connection.on('close', function() {
	   	if(connection.id) { 
                        var idRemove = null;
	   		console.log('Closing connection id { ', connection.id, ' }. ');
			delete users[connection.id]; 
			if(connection.otherid) { 
				console.log('Disconnecting from: ', connection.otherid); 
				var conn = users[connection.otherid]; 
				if(conn != null) {
					conn.otherid = null;
					sendTo(conn, { type: 'leave' }); 
				}  
			} 
                        con.query('CALL sp_delete_patient_in_waitingroom(' + connection.id + ');', function (error, results, fields) {if (error) console.log('Error on query { ', error, ' }. '+'CALL sp_delete_patient_in_waitingroom(' + connection.id + ');'); });
                        for (var i in usersPhysician) {
                            if(connection.id==usersPhysician[i].patientid)
                            {
                                if(null != users[usersPhysician[i].physicianid]) { 
                                    sendTo(users[usersPhysician[i].physicianid], {
                                        type    : 'patientlogout', 
                                        patientid:  usersPhysician[i].patientid,
                                        success : true
                                    });              
                                    idRemove = i;
                                }
                            }
                            else if(connection.id==usersPhysician[i].physicianid)
                            {
                                var userPatient = users[usersPhysician[i].patientid];//get patient element from array                                
                                if(null != userPatient) {
                                    sendTo(userPatient, {
                                        type    : 'check', 
                                        success : false
                                    });                                     
                                }
                            }
                        }
                         if(null != idRemove && conn != idRemove) {
                            usersPhysician.splice(idRemove, 1);
                        }
		}  
	});
});
function sendTo(connection, message) { 
    if(null != connection) {
	connection.send(JSON.stringify(message));
    }
}
function toBuffer(ab) {
    var buffer = new Buffer(ab.byteLength);
    var view = new Uint8Array(ab);
    for (var i = 0; i < buffer.length; ++i) {
        buffer[i] = view[i];
    }
    return buffer;
}
function mkdir(path, root) {
    var dirs = path.split('/'), dir = dirs.shift(), root = (root || '') + dir + '/';

    try { FileSystem.mkdirSync(root); }
    catch (e) { if(!FileSystem.statSync(root).isDirectory()) throw new Error(e); }

    return !dirs.length || mkdir(dirs.join('/'), root);
}
function setBusy(id) {
	if(users[id]) {
	    users[id].status = 'busy';
	    console.log('Set as busy the user ', id);
    }
    else {
    	console.log('Trying to set busy the user ', id, ' but it does not exists');
    }
}