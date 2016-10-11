function WebAnalytics() {

 var _dntStatus = navigator.doNotTrack || navigator.msDoNotTrack;
 var fxMatch = navigator.userAgent.match(/Firefox\/(\d+)/);
 var ie10Match = navigator.userAgent.match(/MSIE 10/i);
 var w8Match = navigator.appVersion.match(/Windows NT 6.2/);

 if (fxMatch && Number(fxMatch[1]) < 32) {
    _dntStatus = 'Unspecified';
 } else if (ie10Match && w8Match) {
     _dntStatus = 'Unspecified';
 } else {
    _dntStatus = { '0': 'Disabled', '1': 'Enabled' }[_dntStatus] || 'Unspecified';
 }
 if (_dntStatus !== 'Enabled'){
     (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
     (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
     m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
     })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

     ga('create', 'UA-84301250-3', 'auto');
     ga('send', 'pageview');
 }

}
WebAnalytics()
