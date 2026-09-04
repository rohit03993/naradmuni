 // Initialize Firebase
  /*Update this config*/
  var config = {
  apiKey: "AIzaSyDfbS00KErQAwcFwhP6Iiey0lAJPO72lnU",
  authDomain: "the-naradmuni.firebaseapp.com",
  databaseURL: "https://the-naradmuni-default-rtdb.firebaseio.com",
  projectId: "the-naradmuni",
  storageBucket: "the-naradmuni.appspot.com",
  messagingSenderId: "678566510077"
  };
  firebase.initializeApp(config);

	// Retrieve Firebase Messaging object.
	const messaging = firebase.messaging();
	messaging.requestPermission()
	.then(function() {
	  console.log('Notification permission granted.');
	  // TODO(developer): Retrieve an Instance ID token for use with FCM.
	  
	  getRegToken();
	  
	  if(isTokenSentToServer()) {
	  	console.log('Token already saved.');
	  } else {
	  	getRegToken();
	  }

	})
	.catch(function(err) {
	  console.log('Unable to get permission to notify.', err);
	});

	function getRegToken(argument) {
		messaging.getToken()
		  .then(function(currentToken) {
		    if (currentToken) {
		      saveToken(currentToken);
		      console.log(currentToken);
		      setTokenSentToServer(true);
		    } else {
		      console.log('No Instance ID token available. Request permission to generate one.');
		      setTokenSentToServer(false);
		    }
		  })
		  .catch(function(err) {
		    console.log('An error occurred while retrieving token. ', err);
		    setTokenSentToServer(false);
		  });
	}

	function setTokenSentToServer(sent) {
	    window.localStorage.setItem('sentToServer', sent ? 1 : 0);
	}

	function isTokenSentToServer() {
	    return window.localStorage.getItem('sentToServer') == 1;
	}

	function saveToken(currentToken) {
		$.ajax({
			url: access_link+'token.php',
			method: 'post',
			data: 'token=' + currentToken
		}).done(function(result){
			console.log(result);
		})
	}

	messaging.onMessage(function(payload) {
	  console.log("Message received. ", payload);
	  notificationTitle = payload.data.title;
	  notificationOptions = {
	  	body: payload.data.body,
	  	icon: payload.data.icon,
	  	image:  payload.data.image,
	  	data: {
            time: new Date(Date.now()).toString(),
            click_action: payload.data.click_action
         }
	  };
	  var notification = new Notification(notificationTitle,notificationOptions);
	});
	
self.addEventListener('notificationclick', function(event) {
  var action_click=event.notification.data.click_action;
  console.log('On notification click: ', event.notification.tag);
  // Android doesn't close the notification when you click on it
  // See: http://crbug.com/463146
  event.notification.close();

  // This looks to see if the current is already open and
  // focuses if it is
  event.waitUntil(
    clients.matchAll({
      type: "window"
    })
    .then(function(clientList) {
      for (var i = 0; i < clientList.length; i++) {
        var client = clientList[i];
        if (client.url == action_click && 'focus' in client)
          return client.focus();
      }
      if (clients.openWindow) {
        return clients.openWindow(action_click);
      }
    })
  );
});