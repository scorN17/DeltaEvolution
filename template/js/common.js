$(document).ready(function(){

/*
	$("[data-fancybox]").fancybox({
		hash: false,
		loop : false,
	});
	$.fancybox.defaults.hash = false;
*/


	jQuery('.bxslider').bxSlider({
        nextText:'',
        prevText:'',
        useCSS: true,
        easing: 'ease-in-out',
        speed: 500,
        auto: true,
        pause: 3000,
        autoHover: true,
        onSlideAfter: function($slideElement, oldIndex, newIndex){
         	renderBlocks( $slideElement.children() , $slideElement);
        },
        onSlideBefore: function($slideElement, oldIndex, newIndex){
         	initItemCardBlock($slideElement.children());
        }
    });


});





function renderBlocks (ctrls , wrapper) { 
    tout = 200;   
    wrapper.show().addClass("active");
    $.each(ctrls , function(key, val){
        setTimeout(function(ctrls){
            renderOneBlock(ctrls)
        } , tout , $(this));
        tout +=200;
    });
}




function renderOneBlock (ctrl) { 
    ctrl.animate({
        opacity: 1
    } , function(){
        });
}


function initItemCardBlock (ctrl){
    ctrl.css({
        opacity:0,
    })
}