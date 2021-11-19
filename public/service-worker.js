importScripts("./js/precache-manifest.f625fc368034522127fb84b4a1be1a1b.js");

importScripts("./js/workbox-sw.js");
importScripts("./js/idb-keyval.js");
importScripts("./js/crypto-js.min.js");

const DATABASE_NAME = "juhaokan-db-v1";
const STORE_NAME = "juhaokan-post";

console.log("DATABASE_NAME", DATABASE_NAME, "STORE_NAME", STORE_NAME);
const dbStore = new idbKeyval.createStore(DATABASE_NAME, STORE_NAME);

if (workbox) {
  console.log(`Yay! Workbox is loaded 🎉`);
} else {
  console.log(`Boo! Workbox didn't load 😬`);
}

// Workbox with custom handler to use IndexedDB for cache.
workbox.routing.registerRoute(
  new RegExp("/gql"),
  // Uncomment below to see the error thrown from Cache Storage API.
  //workbox.strategies.staleWhileRevalidate(),
  async ({ event }) => {
    return staleWhileRevalidate(event);
  },
  "POST"
);

self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
});

// Return cached response when possible, and fetch new results from server in
// the background and update the cache.
self.addEventListener("fetch", async (event) => {
  if (event.request.method === "POST") {
    // console.log('fetch', event.request);
    event.respondWith(staleWhileRevalidate(event));
  }
  // TODO: Handles other types of requests.
});

async function staleWhileRevalidate(event) {
  const cachedResponse = await getCache(event.request.clone());
  const fetchPromise = fetch(event.request.clone())
    .then((response) => {
      setCache(event.request.clone(), response.clone());
      return response;
    })
    .catch((err) => {
      console.error(err);
    });
  return cachedResponse ? Promise.resolve(cachedResponse) : fetchPromise;
}

async function serializeResponse(response) {
  const serializedHeaders = {};
  for (var entry of response.headers.entries()) {
    serializedHeaders[entry[0]] = entry[1];
  }
  const serialized = {
    headers: serializedHeaders,
    status: response.status,
    statusText: response.statusText,
  };
  serialized.body = await response.json();
  return serialized;
}

async function setCache(request, response) {
  const body = await request.json();
  const id = CryptoJS.MD5(body.query + JSON.stringify(body.variables)).toString();

  var entry = {
    query: body.query,
    variables: body.variables,
    response: await serializeResponse(response),
    timestamp: Date.now(),
  };
  // console.log(`setCache==> key:`, id, ' value:', entry);
  idbKeyval.set(id, entry, dbStore);
}

async function getCache(request) {
  let data;
  try {
    const body = await request.json();
    const id = CryptoJS.MD5(body.query + JSON.stringify(body.variables)).toString();
    data = await idbKeyval.get(id, dbStore);
    // console.log(`getCache==> key:`, id, ' value:', data);
    if (!data) return null;

    // Check cache max age.
    const cacheControl = request.headers.get("Cache-Control");
    const maxAge = cacheControl ? parseInt(cacheControl.split("=")[1]) : 3600;
    if (Date.now() - data.timestamp > maxAge * 1000) {
      // console.log('Cache expired. Load from API endpoint.');
      return null;
    }

    // console.log('Load response from cache.');
    return new Response(JSON.stringify(data.response.body), data.response);
  } catch (err) {
    return null;
  }
}

/**
 * The workboxSW.precacheAndRoute() method efficiently caches and responds to
 * requests for URLs in the manifest.
 * See https://goo.gl/S9QRab
 */
self.__precacheManifest = [].concat(self.__precacheManifest || []);
workbox.precaching.precacheAndRoute(self.__precacheManifest, {});
