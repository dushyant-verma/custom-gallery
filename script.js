jQuery(document).ready(function ($) {
    $("[data-fancybox]").fancybox();

    $(".tab-button").on("click", function () {
        var category = $(this).data("category");

        $(".tab-button").removeClass("active");
        $(this).addClass("active");

        if (category === "all") {
            $(".gallery-item").show();
        } else {
            $(".gallery-item").hide();
            $(".gallery-item." + category).show();
        }
    });
});


