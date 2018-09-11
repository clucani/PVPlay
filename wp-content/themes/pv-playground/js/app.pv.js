  /////////////////////////////////////////////////////////////////////////////////////////////
 // Main App
/////////////////////////////////////////////////////////////////////////////////////////////
function pv(container) {

    var app = this;

    app.container = container;
    app.debug = false;

    app.btnFillTestData = jQuery('#btn-test-data', app.container)
    app.xVals = jQuery('#x_values');
    app.yVals = jQuery('#y_values');
    app.tblPoints = jQuery('#tbl-points');

    app.init = function() {
        
        app.initEvents();
        return app;
    }   

    app.initEvents = function() {
        app.btnFillTestData.click(function(e) {
            app.xVals.val('0.1005,0.1002,0.0999,0.0989,0.0980,0.0977,0.0964,0.0950,0.0910,0.0886,0.0686,0.0630,0.0579');
            app.yVals.val('-0.055,-0.097,-0.255,-0.503,-0.821,-0.876,-1.290,-1.876,-2.986,-3.138,-3.966,-4.379,-4.779');
        });

        jQuery('tbody tr', app.tblPoints).click(function(e) {
            row = jQuery(this);

            if(row.hasClass("active")) {
                row.removeClass();
                jQuery('input.point-excluded', row).attr("checked",false);
            }
            else {
                row.addClass("active");
                jQuery('input.point-excluded', row).attr("checked",true);
            }
        });
    }

    return app.init();
}

  /////////////////////////////////////////////////////////////////////////////////////////////
 // Init
/////////////////////////////////////////////////////////////////////////////////////////////
jQuery(function() {
    if(!jQuery('#pv').length) return false;
    window.pvApp = new pv(jQuery('#pv'));

});