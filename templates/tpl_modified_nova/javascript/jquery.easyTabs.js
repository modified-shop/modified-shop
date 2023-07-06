/*
 * Easy Responsive Tabs Plugin
 * Author: Samson.Onna <Email : samson3d@gmail.com>
 * https: *github.com/samsono/Easy-Responsive-Tabs-to-Accordion
 * FIX aria -> https://github.com/samsono/Easy-Responsive-Tabs-to-Accordion/issues/76
 */
! function(t) {
    t.fn.extend({
        easyResponsiveTabs: function(a) {
            var e = a = t.extend({
                    type: "default",
                    width: "auto",
                    fit: !0,
                    closed: !1,
                    activate: function() {}
                }, a),
                s = e.type,
                i = e.fit,
                n = e.width,
                r = "vertical",
                c = "accordion",
                o = window.location.hash,
                d = !(!window.history || !history.replaceState);
            t(this).bind("tabactivate", function(t, e) {
                "function" == typeof a.activate && a.activate.call(e, t)
            }), this.each(function() {
                var e, l = t(this),
                    p = l.find("ul.resp-tabs-list"),
                    b = l.attr("id");
                l.find("ul.resp-tabs-list li").addClass("resp-tab-item"), l.css({
                    display: "block",
                    width: n
                }), l.find(".resp-tabs-container > div").addClass("resp-tab-content"), s == r && l.addClass("resp-vtabs"), 1 == i && l.css({
                    width: "100%",
                    margin: "0px"
                }), s == c && (l.addClass("resp-easy-accordion"), l.find(".resp-tabs-list").css("display", "none")), l.find(".resp-tab-content").wrap("<div class='resp-tab-contents'></div>"), l.find(".resp-tab-content").before("<h2 class='resp-accordion'><span class='resp-arrow'></span></h2>");
                var f = 0;
                l.find(".resp-accordion").each(function() {
                    e = t(this);
                    var a = l.find(".resp-tab-item:eq(" + f + ")"),
                        s = l.find(".resp-accordion:eq(" + f + ")");
                    s.append(a.html()), s.data(a.data()), e.attr("aria-controls", "item-" + f), e.attr("id", "acc_item-" + f), f++
                });
                var v = 0,
                    h = "";
                l.find(".resp-tab-item").each(function() {
                    $tabItem = t(this), $tabItem.attr("aria-controls", "item-" + v), $tabItem.attr("id", "tab_item-" + v), $tabItem.attr("role", "tab");
                    var a = 0;
                    l.find(".resp-tab-content").each(function() {
                        t(this).attr("aria-labelledby", "item-" + a), t(this).attr("id", "item-" + a), t(this).attr("role", "tabpanel"), 1 === l.find('[aria-controls="item-' + a + '"] input:checked').length && (h = a), a++
                    }), v++
                });
                var m = 0;
                if ("" != o) {
                    var u = o.match(new RegExp(b + "([0-9]+)"));
                    null !== u && 2 === u.length && (m = parseInt(u[1], 10) - 1) > v && (m = 0)
                }
                t(l.find(".resp-tab-item")[m]).addClass("resp-tab-active"), !0 === a.closed || "accordion" === a.closed && !p.is(":visible") || "tabs" === a.closed && p.is(":visible") ? t(l.find(".resp-tab-content")[m]).addClass("resp-tab-content-active resp-accordion-closed") : (t(l.find(".resp-accordion")[m]).addClass("resp-tab-active"), t(l.find(".resp-tab-content")[m]).addClass("resp-tab-content-active").attr("style", "display:block")), "" !== h && (t(l.find('.resp-tab-content[aria-labelledby="item-' + h + '"]')).removeClass("resp-tab-content-active resp-accordion-closed"), t(l.find('.resp-tab-content[aria-labelledby="item-' + h + '"]')).addClass("resp-tab-content-active"), t(l.find('.resp-accordion[aria-controls="item-' + h + '"]')).addClass("resp-tab-active")), l.find("[id^=acc_item],[id^=tab_item]").each(function() {
                    t(this).click(function() {
                        var a = t(this),
                            e = a.attr("aria-controls");
                        if (a.hasClass("resp-accordion") && a.hasClass("resp-tab-active")) return l.find(".resp-tab-content-active").slideUp("", function() {
                            t(this).addClass("resp-accordion-closed")
                        }), a.removeClass("resp-tab-active"), !1;
                        if (!a.hasClass("resp-tab-active") && a.hasClass("resp-accordion") ? (l.find(".resp-tab-active").removeClass("resp-tab-active"), l.find(".resp-tab-content-active").slideUp().removeClass("resp-tab-content-active resp-accordion-closed"), l.find("[aria-controls=" + e + "]").addClass("resp-tab-active"), l.find(".resp-tab-content[id = " + e + "]").slideDown().addClass("resp-tab-content-active")) : (l.find(".resp-tab-active").removeClass("resp-tab-active"), l.find(".resp-tab-content-active").removeAttr("style").removeClass("resp-tab-content-active").removeClass("resp-accordion-closed"), l.find("[aria-controls=" + e + "]").addClass("resp-tab-active"), l.find(".resp-tab-content[id = " + e + "]").addClass("resp-tab-content-active").attr("style", "display:block")), a.trigger("tabactivate", a), d) {
                            var s = window.location.hash,
                                i = b + (parseInt(e.substring(5), 6) + 1).toString();
                            if ("" != s) {
                                var n = new RegExp(b + "[0-9]+");
                                i = null != s.match(n) ? s.replace(n, i) : s + "|" + i
                            } else i = "#" + i;
                            tmpUrl = window.location.href, tmpBis = tmpUrl.indexOf("#"), tmpBis > 0 && (tmpUrl = tmpUrl.substr(0, tmpBis)), history.replaceState(null, null, tmpUrl + i)
                        }
                    })
                }), s == c && l.find(".resp-tabs-list").remove(), t(window).resize(function() {
                    l.find(".resp-accordion-closed").removeAttr("style")
                })
            })
        }
    })
}(jQuery);
