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
 * @param urls     array of urls
 * @param callback called with the readable urls (or null if no request got an answer)
 *                 and whether every request got an answer
 */
function webserver_check(urls, callback) {
  var pending = urls.length;
  var answered = 0;
  var exposed = [];

  if (pending === 0 || typeof fetch !== 'function') {
    callback(null, false);
    return;
  }

  urls.forEach(function (url) {
    fetch(url, {cache: 'no-store', credentials: 'omit'})
      .then(function (response) {
        answered++;
        if (response.status !== 200) {
          return;
        }
        // an error page delivered with status 200 is not empty
        return response.text().then(function (body) {
          if (body.trim() === '') {
            exposed.push(url);
          }
        });
      })
      .catch(function () {})
      .then(function () {
        pending--;
        if (pending === 0) {
          callback((answered > 0 ? exposed : null), (answered === urls.length));
        }
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

  webserver_check(urls, function (exposed, complete) {
    var time = Date.now();
    try {
      if (exposed !== null && exposed.length === 0 && complete) {
        localStorage.setItem(key, JSON.stringify({time: time}));
      } else {
        localStorage.removeItem(key);
      }
    } catch (e) {}
    callback(exposed, time);
  });
}
