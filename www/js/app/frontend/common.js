var smoothScrollTo = (function () {
    var timer, start, factor;

    return function (target, duration) {
        var offset = window.pageYOffset,
            delta  = target - window.pageYOffset; // Y-offset difference
        duration = duration || 1000;              // default 1 sec animation
        start = Date.now();                       // get start time
        factor = 0;

        if( timer ) {
            clearInterval(timer); // stop any running animations
        }

        function step() {
            var y;
            factor = (Date.now() - start) / duration; // get interpolation factor
            if( factor >= 1 ) {
                clearInterval(timer); // stop animation
                factor = 1;           // clip to max 1.0
            }
            y = factor * delta + offset;
            window.scrollBy(0, y - window.pageYOffset);
        }

        timer = setInterval(step, 10);
        return timer;
    };
}());

document.addEventListener("DOMContentLoaded", function() {
    var scrollUp =  document.getElementById("scrollUp");
    scrollUp.addEventListener('click', function(){
        smoothScrollTo(0, 500);
    });
    var menuBtn =  document.getElementById("mobileMenuBtn");
     menuBtn.addEventListener('click', function(){
        var topBlocks = document.getElementById("topBlocks");
        if(!topBlocks){
             return;
        }
        if(topBlocks.style.display !== 'block'){
            topBlocks.style.display = 'block';
        }else{
            topBlocks.style.display = 'none';
        }
    });
    window.addEventListener('scroll',function() {
        var scrolled = window.pageYOffset || document.documentElement.scrollTop;

        if(scrolled > 100){
            scrollUp.style.display = 'block';
        }else{
            scrollUp.style.display = 'none';
        }
    });
});