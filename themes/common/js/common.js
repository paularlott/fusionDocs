!function(){var a=window.jQuery||window.fusionLib,t=0,e="",s=!1;a.fn.tabWidget||(a.fn.tabWidget=function(a){function t(){for(var a in l)if(l[a][1].find(".validation").hasClass("failed")){l[a][0].trigger("click");var t=$("label.validationError").first().attr("for");setTimeout(function(){$("#"+t).focus()},100);break}}var e,s,n=this[0].id,l={};if("1"!=this.attr("data-tabs-enabled")){this.attr("data-tabs-enabled","1"),null!=(s=this.attr("data-tabs-active"))&&(e=s),this.find(".tabs li").each(function(){var t,s,o=$(this);o.parent().parent()[0].id==n&&(null==(t=o.attr("data-tabpanel"))&&(t=o.find("a").attr("href").replace(/^.*#/,"")),s=$("#"+t),(void 0==e||void 0!=a&&a==t)&&(e=t),t!=e&&s.addClass("tabhidepanel").attr("aria-expanded",!1),o.parent().attr("aria-selected",!1),l[t]=[o,s],o.on("click",function(a){return l[e][1].addClass("tabhidepanel").attr("aria-expanded",!1),l[t][1].removeClass("tabhidepanel").attr("aria-expanded",!0),l[e][0].removeClass("active").parent().attr("aria-selected",!1),l[t][0].addClass("active").parent().attr("aria-selected",!0),e=t,o.trigger("fcss:tabclick"),a.preventDefault(),a.stopPropagation(),!1}))}),void 0!=e&&(l[e][0].addClass("active").parent().attr("aria-selected",!0),l[e][1].removeClass("tabhidepanel").attr("aria-expanded",!0));for(var o=this[0];o;){if("FORM"==o.tagName){$(o).on("formValidationFailed",t).addClass("tabwidgetcontainer"),t();break}o=o.parentNode}}return this}),a.fn.extend({toastShow:function(a,n,l,o,i){var r=0;return s||($("body").append('<div id="toast" role="alert" aria-hidden="true"></div>'),s=!0),e=a,l&&(n='<a href="#">'+l+"</a>"+n),t?(clearTimeout(t),t=0,r=300):$("#toast").hasClass("exposed")&&(r=300),$("#toast").removeClass("exposed").removeClass("success").removeClass("error").removeClass("warning").removeClass("accent").removeClass("primary").attr("aria-hidden",!0),setTimeout(function(){$("#toast").html(n).addClass("exposed").addClass(a).attr("aria-hidden",!1),l&&$("#toast a").on("click",function(a){t&&(clearTimeout(t),t=0),$("#toast").removeClass("exposed").attr("aria-hidden",!0),o&&o(),a.preventDefault()}),(!l&&!1!==i||!0===i)&&(t=setTimeout(function(){$("#toast").removeClass("exposed").removeClass(a).attr("aria-hidden",!0),t=0},5e3))},r),this},toastHide:function(){t&&(clearTimeout(t),t=0),$("#toast").removeClass("exposed").removeClass(e).attr("aria-hidden",!0)}}),a.toastShow=a.fn.toastShow,a.toastHide=a.fn.toastHide,$(document).ready(function(){function t(){var a=$(window);a.scrollTop()>0&&(a.scrollTop(Math.max(0,a.scrollTop()-Math.max(10,a.scrollTop()/20))),window.setTimeout(t,10))}function e(){var a=$(this),t=$("#"+a.attr("id")+"-label"),e=a.attr("type");"checkbox"!=e&&"submit"!=e&&"file"!=e&&a.on("focus",function(){t.addClass("focused")}).on("blur",function(){t.removeClass("focused")})}function s(){var a=$(this),t=a.attr("type");if("disabled"!=a.attr("data-floating-label")&&!a.hasClass("hasFloatingLabel")&&"checkbox"!=t&&"submit"!=t&&"file"!=t){var e=$("#"+a.attr("id")+"-label");e.length&&(a.is("textarea")&&e.addClass("floatTextarea"),a.addClass("hasFloatingLabel"),a.on("focus",function(){e.removeClass("floatDown").addClass("floatUp"),void 0!=a.attr("data-placeholder")&&a.attr("placeholder",a.attr("data-placeholder"))}).on("blur",function(){a.is("select")||a.val()||a.hasClass("keepPlaceholder")||a.attr("data-keep-placeholder")?e.removeClass("floatDown").addClass("floatUp"):e.addClass("floatDown").removeClass("floatUp"),void 0!=a.attr("data-placeholder")&&a.attr("placeholder","")}).on("change",function(){e.removeClass("focused"),a.is("select")||a.val()||a.hasClass("keepPlaceholder")||a.attr("data-keep-placeholder")?e.removeClass("floatDown").addClass("floatUp"):e.addClass("floatDown").removeClass("floatUp")}),a.hasClass("keepPlaceholder")||a.attr("data-keep-placeholder")||void 0!=a.attr("placeholder")&&(a.closest(".placeholderOnFocus").length&&a.attr("data-placeholder",a.attr("placeholder")),a.attr("placeholder","")),a.trigger("blur"))}}function n(){var a=$(this);a.hasClass("hform")||(a.find("input").each(s),a.find("textarea").each(s),a.find(".selectControl select").each(s))}$(".tabwidget").each(function(){$(this).tabWidget()}),$(".uploadButton input").on("change",function(a){$(this).parent().find("span").html($(this).val().split(/(\\|\/)/g).pop())}),$("table.responsive").each(function(a,t){$(t).wrap('<div class="responsiveTableWrapper" />'),$(t).wrap('<div class="responsiveTableWrapperInner" />')}),$("#viewSlideInMenu").length&&($("body").append('<div id="slideInMenuOverlay"></div>'),$("#slideInMenu").length||($("body").append('<div id="slideInMenu" role="menu"></div>'),$(".slideInMenu").each(function(a){var t="",e="";"ul"==this.nodeName.toLowerCase()&&(t="<ul>",e="</ul>"),$(this).hasClass("slideInMenuRootOnly")?($("#slideInMenuOverlay").html(t+$(this).html()+e).find("li ul").remove(),$("#slideInMenu").append($("#slideInMenuOverlay").html())):$("#slideInMenu").append(t+$(this).html()+e)})),$("#slideInMenu").attr("aria-hidden",!0),$("#slideInMenuOverlay").html("").on("click",function(a){$("#slideInMenu").removeClass("slideInMenuShow").attr("aria-hidden",!0),$("#slideInMenuOverlay").removeClass("slideInMenuShow"),$("body").removeClass("disableScroll")}),$("#viewSlideInMenu").on("click",function(a){$("body").addClass("disableScroll"),$("#slideInMenuOverlay").addClass("slideInMenuShow"),$("#slideInMenu").addClass("slideInMenuShow").attr("aria-hidden",!1)}));var l=$("#scrollToTop");l.length&&(l.on("click",t),$(window).on("scroll",function(){$(this).scrollTop()>(null!=l.attr("data-showat")?l.attr("data-showat"):600)?l.removeClass("hide"):l.addClass("hide")}));var o=a.fn.val;a.fn.val=function(t){if(void 0===t)return o.call(this);var e=a(this[0]),s=a("#"+e.attr("id")+"-label");return e.hasClass("hasFloatingLabel")&&"checkbox"!=e.attr("type")&&("string"!=typeof t||t.length||e.is("select")||document.activeElement==this[0]?(s.removeClass("floatDown").addClass("floatUp"),void 0!=e.attr("data-placeholder")&&e.attr("placeholder",e.attr("data-placeholder"))):(s.addClass("floatDown").removeClass("floatUp"),void 0!=e.attr("data-placeholder")&&e.attr("placeholder",""))),o.call(this,t)};var i=a.fn.focus;a.fn.focus=function(){i.call(this);var t=a(this[0]),e=a("#"+t.attr("id")+"-label");return t.hasClass("hasFloatingLabel")&&"checkbox"!=t.attr("type")&&(e.removeClass("floatDown").addClass("floatUp"),e.addClass("focused"),void 0!=t.attr("data-placeholder")&&t.attr("placeholder",t.attr("data-placeholder"))),this},$(".floatingLabels form").each(n),$("form.floatingLabels").each(n),$("form").each(function(){var a=$(this);a.hasClass("hform")||(a.find("input").each(e),a.find("textarea").each(e),a.find("select").each(e))}),setTimeout(function(){$(document.activeElement).trigger("focus")},100);var r=!1;$(".fam").each(function(){var a=$(this),t=a.find("ul");t.attr("aria-hidden",!t.hasClass("exposed")),a.find("ul.alwaysOpen").length||a.find("a").first().on("click",function(a){t.hasClass("exposed")?t.removeClass("exposed").attr("aria-hidden",!0):($(".fam ul").removeClass("exposed").attr("aria-hidden",!0),t.addClass("exposed").attr("aria-hidden",!1)),a.preventDefault()}).attr("data-fam-menu",1),r=!0}),r&&$(document).on("click",function(a){var t=$(a.target);"HTML"!=a.target.nodeName&&(t.attr("data-fam-menu")||t.hasClass("leave-open")||t.parent().attr("data-fam-menu")||t.parent().hasClass("leave-open"))||$(".fam ul").removeClass("exposed").attr("aria-hidden",!0)})})}(),function(){function a(a,t){s>=e[a].top&&e[a].before?(e[a].before=!1,e[a].handler.call(e[a].element,t),e[a].element.trigger("pointReached",[t])):s<=e[a].top&&!e[a].before&&(e[a].before=!0,e[a].handler.call(e[a].element,t),e[a].element.trigger("pointReached",[t]))}var t=window.jQuery||window.fusionLib,e=[],s=t(window).scrollTop(),n=[];t.fn.extend({trackPoint:function(n){var l=(n=n||{}).offset?n.offset:0,o=n.handler?n.handler:function(){},i="string"==typeof l&&l.match(/%$/)?"%":"px";return this.each(function(){var n=t(this),r={element:n,top:n.offset().top+("%"==i?parseInt(l)/100*t(window).height():parseInt(l)),before:!0,offset:l,handler:o};e.push(r),n.wrap('<div class="trackPointWrapper"></div>'),s>=r.top&&a(e.length-1,"down")})},trackPointSetOffset:function(s){var n="string"==typeof s&&s.match(/%$/)?"%":"px";return this.each(function(){for(var l=0;l<e.length;l++)if(this===e[l].element.get(0)){var o=e[l].top,i=e[l].element.parent().offset().top+("%"==n?parseInt(s)/100*t(window).height():parseInt(s));e[l].offset=s,e[l].top=i,o!=e[l].top&&a(l,e[l].top>o?"up":"down");break}})}}),t(window).on("scroll",function(){var n=t(window).scrollTop(),l=n>s?"down":"up";if(n!=s){s=n;for(var o=0;o<e.length;o++)a(o,l)}}).on("resize",function(){for(var s=0;s<e.length;s++){var n=e[s].top,l="string"==typeof e[s].offset&&e[s].offset.match(/%$/)?"%":"px",o=e[s].element.parent().offset().top+("%"==l?parseInt(e[s].offset)/100*t(window).height():parseInt(e[s].offset));e[s].top=o,n!=o&&a(s,e[s].top>n?"up":"down")}}),t.fn.extend({stickyOnScroll:function(a){return a=a||{},a.stuckClass=a.stuckClass?a.stuckClass:"stuck",a.handler=a.handler?a.handler:function(){},a.minWidth=a.minWidth?a.minWidth:null,a.maxWidth=a.maxWidth?a.maxWidth:null,a.offset=a.offset?a.offset:0,a.stuck=!1,this.each(function(){var e=t(this);e.get(0)._stickyOpts=Object.assign({},a),e.trackPoint({handler:function(a){var t=this.get(0)._stickyOpts;if("down"==a){e=$(window).width();(null==t.minWidth||e>=t.minWidth)&&(null==t.maxWidth||e<=t.maxWidth)&&(t.stuck=!0,this.parent().height(this.outerHeight(!0)),this.addClass(t.stuckClass),t.handler.call(this,"stuck"),this.trigger("stuck"))}else{var e=$(window).width();(t.stuck||(null==t.minWidth||e>=t.minWidth)&&(null==t.maxWidth||e<=t.maxWidth))&&(t.stuck=!1,this.parent().height(""),this.removeClass(t.stuckClass),t.handler.call(this,"unstuck"),this.trigger("unstuck"))}},offset:a.offset}),e.parent().addClass("stickyWrapper"),n.push(e)})},stickyOnScrollOffset:function(a){return this.each(function(){this._stickyOpts.offset=a,$(this).trackPointSetOffset(a)})}}),t(window).on("resize",function(){for(var a=0;a<n.length;a++)if(n[a].get(0)._stickyOpts.stuck){var t=n[a];t.parent().height(t.outerHeight(!0))}})}();