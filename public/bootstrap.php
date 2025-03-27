<html>
<body>
<h1>Starting Server</h1>
<div id="message"></div>
<script>
    function log(msg) {
        var message = document.getElementById('message');
        message.innerHTML += msg + '<br>';
    }
    var host = window.location.hostname;
    log(host);
    var url = 'http://' + host + ':8080/MBuddy/public/server_ipad.php';
    log(url);

    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", url, true );
    xmlHttp.send( null );

    setTimeout(function() {
        var mbuddy = 'http://' + host + ':8383/MBuddy/';
        log(mbuddy);
        window.location.assign(mbuddy);
    }, 5000);
</script>
</body>
</html>