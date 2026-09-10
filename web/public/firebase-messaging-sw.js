/* The Naradmuni — PWA + FCM (compat with existing legacy FCM server) */
importScripts("https://www.gstatic.com/firebasejs/8.10.2/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.2/firebase-messaging.js");

firebase.initializeApp({
  apiKey: "AIzaSyDfbS00KErQAwcFwhP6Iiey0lAJPO72lnU",
  authDomain: "the-naradmuni.firebaseapp.com",
  databaseURL: "https://the-naradmuni-default-rtdb.firebaseio.com",
  projectId: "the-naradmuni",
  storageBucket: "the-naradmuni.appspot.com",
  messagingSenderId: "678566510077",
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
  const data = (payload && payload.data) || {};
  const n = (payload && payload.notification) || {};
  const title = data.title || n.title || "The Naradmuni";
  const options = {
    body: data.body || n.body || "",
    icon: data.icon || "/icons/nm-192.png",
    badge: "/icons/nm-192.png",
    image: data.image || undefined,
    data: {
      click_action: data.click_action || "/",
    },
    tag: data.click_action || "naradmuni-news",
    renotify: true,
  };
  return self.registration.showNotification(title, options);
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.click_action) || "/";
  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then(function (clientList) {
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url.indexOf(self.location.origin) === 0 && "focus" in client) {
          if ("navigate" in client) client.navigate(url);
          return client.focus();
        }
      }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});

self.addEventListener("install", function () {
  self.skipWaiting();
});
self.addEventListener("activate", function (event) {
  event.waitUntil(clients.claim());
});
self.addEventListener("fetch", function (event) {
  event.respondWith(
    fetch(event.request).catch(function () {
      return caches.match(event.request);
    })
  );
});
