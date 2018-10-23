function addScript(filepath, callback) {
    if (filepath) {
        var fileref = document.createElement('script');
        var done = false;
        var head = document.getElementsByTagName("head")[0];

        fileref.onload = fileref.onreadystatechange = function () {
            if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                done = true;

                callback();

                // Handle memory leak in IE
                fileref.onload = fileref.onreadystatechange = null;
                if (head && fileref.parentNode) {
                    head.removeChild(fileref);
                }
            }
        };

        fileref.setAttribute("type", "text/javascript");
        fileref.setAttribute("src", filepath);

        head.appendChild(fileref);
    }
}

var src = '//vk.com/js/api/openapi.js?152';

addScript(src, function() {
    $('<div id="vk_community_messages"></div>').appendTo('body');
    VK.Widgets.CommunityMessages("vk_community_messages", 122154011, {disableExpandChatSound: "1",tooltipButtonText: "Есть вопрос?"});
});

