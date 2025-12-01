$(document).ready(function(){
    $.ajax({
        url: "generate_work_order.php",
        method: "GET",
        success: function(data){
            $('#work_order_no').val(data);
        }
    });
});

$(document).ready(function(){
    $.ajax({
        url: "generate_work_order_recurring.php",
        method: "GET",
        success: function(data){
            $('#work_order_no_recurring').val(data);
        }
    });
});