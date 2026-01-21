document.addEventListener("DOMContentLoaded", () => {
    console.log("Booking Completed!");
    var msgOverlay = document.createElement("div");
    msgOverlay.innerHTML = '<span>Redirecting to Leiths. Please wait...</span>';
    msgOverlay.style.cssText = "position:fixed;top:0;left:0;width:100vw;height:100vh;opacity:1;z-index:99999;background:#fff;font-size:1.2em;text-align:center;color:#000;display:flex;flex:1;align-items:center;justify-content:center";
    document.body.appendChild(msgOverlay);
    window.setTimeout(function () {
        window.location.href = "https://wordpress-1041599-3959257.cloudwaysapps.com/booking-confirmation/";
    }, 3000);
});
