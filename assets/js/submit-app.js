$(document).ready(function () {
  $("#submit-loader").hide();


  $("#submit-form").on("submit", function (e) {
    e.preventDefault();

    $("#submit-loader").show();
    $("#submit-form").hide();

    var formData = new FormData(this);

    $.ajax({
      url: "assets/php/send-mail.php",
      type: "post",
      data: formData,
      processData: false,
      contentType: false,
      success: function (status) {
        $("#submit-form").show();
        $("#submit-loader").hide();
        $("#enq-email").val("");
        $("#enq-name").val("");
        $("#enq-phone").val("");
        $("#enq-address").val("");
        $("#alert").append(`<div class="alert">Application Submitted Successfully!</div>`);
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        $("#submit-loader").hide();
        $("#submit-form").show();
        alert(errorThrown);
      }
    });
  });
});
