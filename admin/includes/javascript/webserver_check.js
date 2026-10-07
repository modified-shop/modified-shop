/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/**
 * Loads shipped empty files from protected directories in the admin's browser.
 *
 * A server side request could be stopped by firewalls or basic authentication and report
 * a false result, the browser sees the shop like a visitor does. Credentials are omitted,
 * so files behind basic authentication do not count as public.
 *
 * Every probe ends after 10 seconds at the latest, so a stalled response cannot hold back
 * the result or an exposure found by another probe.
 *
 * @param urls     array of urls
 * @param callback called with the readable urls; an empty array only if every file gave a
 *                 clear answer, null if nothing readable was found but the result is open
 */
function webserver_check(urls, callback) {
  var pending = urls.length;
  var determined = 0;
  var exposed = [];

  if (pending === 0 || typeof fetch !== 'function') {
    callback(null);
    return;
  }

  urls.forEach(function (url) {
    var settled = false;
    var controller = (typeof AbortController === 'function') ? new AbortController() : null;
    var timer = setTimeout(function () {
      if (controller) {
        controller.abort();
      }
      settle('open');
    }, 10000);

    // counts each probe exactly once: exposed, closed or open
    function settle(state) {
      if (settled) {
        return;
      }
      settled = true;
      clearTimeout(timer);
      if (state === 'exposed') {
        exposed.push(url);
      }
      if (state !== 'open') {
        determined++;
      }
      pending--;
      if (pending === 0) {
        callback((exposed.length > 0 || determined === urls.length) ? exposed : null);
      }
    }

    fetch(url, {cache: 'no-store', credentials: 'omit', signal: (controller ? controller.signal : undefined)})
      .then(function (response) {
        if (response.status === 200) {
          // compare parsed urls, so percent encoding of spaces or umlauts is no redirect;
          // another address or a missing url tells nothing about the probe
          try {
            var requested = new URL(url, document.baseURI);
            var received = new URL(response.url);
            requested.hash = '';
            received.hash = '';
            if (requested.href !== received.href) {
              settle('open');
              return;
            }
          } catch (e) {
            settle('open');
            return;
          }
          // an error page delivered with status 200 is not empty
          return response.text().then(function (body) {
            settle((body.trim() === '') ? 'exposed' : 'closed');
          });
        }
        // refused or hidden; server errors, timeouts and rate limits tell nothing
        settle((response.status >= 400 && response.status < 500 && response.status !== 408 && response.status !== 429) ? 'closed' : 'open');
      })
      .catch(function () {
        settle('open');
      });
  });
}

/**
 * Runs webserver_check() at most once a day per browser while it passes.
 *
 * Apache logs every denied probe as an error that fail2ban may count against the admin's IP.
 *
 * @param urls     array of urls
 * @param callback called with the readable urls (or null) and the time of the check
 * @param force    true runs the check even if a passed result is stored
 */
function webserver_check_cached(urls, callback, force) {
  var key = 'webserver_check:' + urls.join('|');
  var stored = null;

  try {
    stored = JSON.parse(localStorage.getItem(key));
  } catch (e) {}

  if (force !== true && stored && typeof stored.time === 'number' && Date.now() - stored.time < 86400000) {
    callback([], stored.time);
    return;
  }

  // a requested repeat drops the stored pass at once, even if the page is left before the result
  if (force === true) {
    try {
      localStorage.removeItem(key);
    } catch (e) {}
  }

  webserver_check(urls, function (exposed) {
    var time = Date.now();
    try {
      if (exposed !== null && exposed.length === 0) {
        localStorage.setItem(key, JSON.stringify({time: time}));
      } else {
        localStorage.removeItem(key);
      }
    } catch (e) {}
    callback(exposed, time);
  });
}
