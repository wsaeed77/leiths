// Init global namespace
var WPJB = WPJB || {};

// Init CF namespace
WPJB.CF = WPJB.CF || {};

WPJB.CF.DateField = function(field) {
    this.field = field;
    this.full = field.find("input.wpjb-date-picker");
    this.date = field.find("input.wpjb-date-picker");
    
    this.date.datepicker({
        dateFormat: this.date.data("date-format"),
        //autoSize: true,
        changeMonth: false,
        changeYear: true,
        yearRange: "c-10:c+10",
        onSelect: jQuery.proxy(this.Sync, this)
    });
    
    this.date.on("blur", jQuery.proxy(this.SyncBlur, this));    
};

WPJB.CF.DateField.prototype.Sync = function() {
    
    var pc = this.date.datepicker( "getDate" );
    var full = "";
    
    full  = pc.getFullYear().toString() + "-";
    full += (pc.getMonth()+1).toString().padStart(2, '0') + "-"
    full += pc.getDate().toString().padStart(2, '0');

    this.full.val(full);
};

WPJB.CF.DateField.prototype.SyncBlur = function() {
    if(this.date.val().length > 0) {
        this.full.val("");
        this.date.setDate("Today");
        return;
    }
    
};

jQuery(function($) {
    $(".input.wpjb-date-picker").each(function(index, item) {
        new WPJB.CF.DateField($(item));
    });
});