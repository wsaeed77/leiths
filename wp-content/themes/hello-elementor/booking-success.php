<?php
/** Template Name: Booking Success Redirection **/
?>
<html>
<title>Leiths - Booking Success</title>
<script src="/wp-content/themes/hello-elementor/assets/js/leiths.js"></script>
<script>
    var lts_cookie = getCookie("Leiths_PJS");
    window.location.href = "/booking-confirmation?ref=" + lts_cookie;
</script>
</html>
<body>
</body>
</html>

