jQuery('body').on('MPCEObjectCreated MPCEObjectUpdated', function(event, wrapper, shortcodeName){

    _type = event.type;
    _wrapper = wrapper;
    _children = wrapper.children();

    /*switch (shortcodeName) {
        case (mpce_cherry4_prefix + 'google_map') : {
            CHERRY_API.shortcode.google_map.init();
            break;
        }
        case (mpce_cherry4_prefix + 'swiper_carousel') : {
            CHERRY_API.shortcode.swiper_carousel.init();
            break;
        }
        default : {
            if (!!$.prototype.init)
                _children.init();
        }
    }*/

    _name =  shortcodeName.replace( new RegExp('^' + mpce_cherry4_prefix), '');
    console.log(_name);

    if ( _name in CHERRY_API.shortcode ) {
        console.log('init');
        CHERRY_API.shortcode[_name].init();
    }

});