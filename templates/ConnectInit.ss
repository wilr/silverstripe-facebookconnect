<div id="fb-root"></div>
<script>
    window.fbAsyncInit = function() {
      FB.init({
        appId      : '$Top.FacebookAppId',
        status     : true, 
        cookie     : true,
        xfbml      : true,
        oauth      : true,
      });

      FB.Event.subscribe('auth.login', function() {
        window.location.reload();
      });
    };
    (function(d){
       var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
       js = d.createElement('script'); js.id = id; js.async = true;
       js.src = "//connect.facebook.net/$Top.FacebookLanguage/all.js";
       d.getElementsByTagName('head')[0].appendChild(js);
     }(document));
</script>