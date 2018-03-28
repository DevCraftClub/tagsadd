<script src="{THEME}/modules/tagsadd/assets/tokenfield.min.js"></script>
<link href="{THEME}/modules/tagsadd/assets/tagsadd.css" rel="stylesheet" type="text/css">
<script>
$(document).ready(function() {
    $('#{name}_button').click( function(event){
        event.preventDefault();
        $('.tagsadd_overlay').fadeIn(400, function(){
            $('.{name}_modal').css('display', 'block').animate({opacity: 1, top: '50%'}, 200);
        });
    });
    $('.tagsadd_close, .tagsadd_overlay').click( function(){
        $('.{name}_modal').animate({opacity: 0, top: '45%'}, 200, function(){
            $(this).css('display', 'none');
            $('.tagsadd_overlay').fadeOut(400);
        });
    });
    $('#newtags').tokenfield();
    $('#newtags').on('tokenfield:createtoken', function (event) {
       var existingTokens = $(this).tokenfield('getTokens');
       $.each(existingTokens, function(index, token) {
           if (token.value === event.attrs.value)
               event.preventDefault();
       });
   });
});
</script>