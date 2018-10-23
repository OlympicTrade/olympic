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

var src = '//www.googletagmanager.com/gtag/js?id=UA-113457484-1';

addScript(src, function() {
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-113457484-1');
});

