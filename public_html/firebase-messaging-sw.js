importScripts('https://www.gstatic.com/firebasejs/4.9.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/4.9.1/firebase-messaging.js');
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

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = payload.data.title;
  const notificationOptions = {
    body: payload.data.body,
	  	icon: payload.data.icon,
	  	image: payload.data.image,
        sound: payload.data.sound,
	  	data: {
            time: new Date(Date.now()).toString(),
            click_action: payload.data.click_action
         }
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
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