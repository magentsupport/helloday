var EsNewsSubscribers = {

    cookieLiveTime: 100,

    cookieName: 'es_newssubscriber',

    baseUrl: '',

    setCookieLiveTime: function(value)
    {
        this.cookieLiveTime = value;
    },

    setCookieName: function(value)
    {
        this.cookieName = value;
    },

    setBaseUrl: function(url)
    {
        this.baseUrl = url;
    },

    getBaseUrl: function(url)
    {
        return this.baseUrl;
    },

    createCookie: function() {
        var days = this.cookieLiveTime;
        var value = 1;
        var name = this.cookieName;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = escape(name)+"="+escape(value)+expires+"; path=/";
    },

    readCookie: function(name) {
        var name = this.cookieName;
        var nameEQ = escape(name) + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length,c.length));
        }
        return null;
    },

    boxClose: function()
    {
        jQuery('#esns_background_layer').hide();
    },

    boxOpen: function()
    {
        jQuery('#esns_background_layer').show();
    }
};

jQuery(function() {
    if (EsNewsSubscribers.readCookie() != 1) {
        EsNewsSubscribers.createCookie();
        var newsele = "";
        if(jQuery('img.popupMobImg').length) { 
            newsele = jQuery('img.popupMobImg'); 
        } else { 
            newsele = jQuery('img.popupImg');
        } 

        jQuery(newsele).load(function() {
            EsNewsSubscribers.boxOpen();
        });
        
    }
    //jQuery('#esns_background_layer').css('height', jQuery(document).height()+'px');
    //jQuery('#esns_box_layer').css('margin-top', (jQuery('#esns_box_layer').height())+'px');
    jQuery('#esns_submit').click(function(){
        var email = jQuery('#esns_email').val();
        var first_name = jQuery('#esns_name').val();
        jQuery.post(EsNewsSubscribers.getBaseUrl()+'newsletter/subscriber/newajax/', {'email':email, 'first_name':first_name}, function(resp) {
            if (resp.errorMsg) {
                jQuery('#esns_box_subscribe_response_error').html(resp.errorMsg);
            } else {
                jQuery('#esns_box_subscribe_response_error').html('');
               jQuery('#esns_box_block_1').html('Thank you '+ resp.successMsg);
               /*jQuery('#esns_box_block_2').html(resp.successMsg);*/
               /* jQuery('#esns_box_block_2').css('width','380px');*/
               jQuery('#esns_box_block_1').css({'text-transform':'none','margin-top':'100px'});
               jQuery('#esns_box_block_2').css({'width':'380px',"color":"#f9a63a",'display':'none'});
                jQuery('#esns_box_title').css('margin-top','40px');
                
                //jQuery('#esns_box_subscribe_response_success').html(resp.successMsg);
                jQuery('#esns_box_subscribe_form').css('display','none');
                jQuery('#esns_box_block_3').css('display','none');
                jQuery('#esns_box_layer').css('height','355px');
                
                //jQuery('#esns_box_subscribe_response_success').css('display','block');
                setTimeout('EsNewsSubscribers.boxClose()', 15000)
            }
        });
    });
    jQuery('#esns_box_close').click(function(){
        EsNewsSubscribers.boxClose();
    });

    
    jQuery('#esns_box_block_3 a').attr('target', '_blank');


});

jQuery.noConflict();