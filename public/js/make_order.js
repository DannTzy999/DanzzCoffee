const setVisible = (elementOrSelector, visible) =>
    ((typeof elementOrSelector === "string"
        ? document.querySelector(elementOrSelector)
        : elementOrSelector
    ).style.display = visible ? "block" : "none");

// Preview Bukti Pembayaran
function previewProofPayment(input) {
    const file = input.files[0];
    if (file) {
        // Validasi file size (max 2MB)
        if (file.size > 2048000) {
            alert("Ukuran file terlalu besar! Maksimal 2MB");
            input.value = "";
            return;
        }
        
        // Validasi tipe file
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert("Format file tidak didukung! Gunakan PNG, JPG, atau GIF");
            input.value = "";
            return;
        }
        
        // Preview gambar
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("proof_preview_image").src = e.target.result;
            document.getElementById("proof_file_name").textContent = file.name;
            document.getElementById("proof_empty_container").style.display = "none";
            document.getElementById("proof_preview_container").style.display = "block";
        };
        reader.readAsDataURL(file);
    }
}

function clearProofPreview() {
    document.getElementById("proof_payment_file").value = "";
    document.getElementById("proof_preview_container").style.display = "none";
    document.getElementById("proof_empty_container").style.display = "block";
    document.getElementById("proof_preview_image").src = "";
    document.getElementById("proof_file_name").textContent = "";
}

function hideMessage(payment_method) {
    if (
        $("#" + payment_method + "_alert").html() != null &&
        $("#" + payment_method + "_alert").css("display") != "none"
    ) {
        $("#" + payment_method + "_alert").css("display", "none");
    } else if (payment_method == "cod") {
        $("#qr_alert").css("display", "block");
    } else if (payment_method == "qr") {
        $("#cod_alert").css("display", "block");
    } else {
        $("#cod_alert").css("display", "block");
    }
}

function hideBankMessage() {
    $("#bank_id_alert").css("display") != "none";
}

var isUseCoupon = false;
var couponTotal;
var currentNum = 0;
var couponUsed = 0;

window.onload = function () {
    couponTotal = parseInt($("#coupon").attr("data-valueCoupon"));
    if ($("#couponUsedShow").attr("data-couponUsed") != null) {
        couponUsed = parseInt($("#couponUsedShow").attr("data-couponUsed"));
    }
    $("#couponUsedShow").html(`${couponUsed} coupon`);
};

function changeStatesCoupon() {
    isUseCoupon = !isUseCoupon;
}

// ================ order summary ==================
var sub_total;
var total;
var shipping;
// ===============================================

// counter order summary [buat ]
function myCounter() {
    var num = parseInt(document.getElementById("quantity").value);
    var price = parseInt(
        document.getElementById("price").getAttribute("data-truePrice")
    );
    shipping = parseInt(
        document.getElementById("shipping").getAttribute("data-shippingCost")
    );

    if (quantity != null && product_id != null && destinasi != null) {
        setOngkir({ destination: destinasi, quantity: num });
    }

    if (isUseCoupon && couponTotal > 0 && currentNum < num) {
        // ketika user menggunakan coupon
        couponTotal = couponTotal - 1;
        couponUsed = couponUsed + 1;
    } else if (isUseCoupon && couponUsed > 0 && currentNum > num) {
        couponTotal = couponTotal + 1;
        couponUsed = couponUsed - 1;
    } else if (!isUseCoupon && couponUsed > 0) {
        couponTotal = couponTotal + 1;
        couponUsed = couponUsed - 1;
    }

    sub_total = price * (num - couponUsed);
    total = sub_total + shipping;

    $("#coupon").html(`${couponTotal} coupon`);
    $("#couponUsed").val(couponUsed);
    $("#couponUsedShow").html(`${couponUsed} coupon`);

    refresh_data({ sub_total: sub_total, total: total });
    currentNum = num;
}

function refresh_data({ sub_total = 0, shipping = 0, total = 0 }) {
    if (total >= 0) {
        $("#total_price").val(total);
        $("#total").html(total);
        $("#total_price_qr").html(total);
    }
    if (sub_total >= 0) {
        $("#sub-total").html(sub_total);
    }
    if (shipping >= 0) {
        $("#shipping").attr("data-shippingCost", shipping);
        $("#shipping").html(shipping);
    }
}

// ===================================  Ongkir  =======================================
// ==== DATA ====
var product_id;
var destinasi;
var quantity;

// ==============

// Text input handlers for province and city
$("#province").on("change blur", function (e) {
    // When province changes, just accept the text input
    setDestination();
});

$("#city").on("change blur", function (e) {
    // When city changes, set destination
    setDestination();
});

function setDestination() {
    var province = $("#province").val().trim();
    var city = $("#city").val().trim();
    
    if (province === "" && city === "") {
        destinasi = "0";
        return;
    }
    
    // Combine province and city for destination
    destinasi = (city && province) ? city + ", " + province : (city || province);
    
    // Update shipping address
    $("#shipping_address").val(destinasi);
    
    product_id = $("#quantity").attr("data-productId");
    quantity = $("#quantity").val();
    
    // Only set ongkir if we have valid destination and quantity
    if (destinasi !== "0" && destinasi !== "" && quantity > 0) {
        setOngkir({
            destination: destinasi,
            quantity: quantity,
            product_id: product_id,
        });
    }
}

function setOngkir({
    origin = 42, // banyuwangi
    destination,
    quantity,
    courier = "jne",
}) {
    if (quantity == 0) {
        refresh_data({
            shipping: 0,
            sub_total: 0,
            total: 0,
        });

        return;
    }
    
    // Check if destination is numeric (city_id) or string (city name)
    var destinationNum = parseInt(destination);
    quantity = parseInt(quantity);
    
    // If destination is not a valid number, use a default shipping cost or skip
    if (isNaN(destinationNum) || destinationNum == 0) {
        // For manual text input, use a default shipping cost or you can adjust this
        var defaultShipping = 50000; // Default shipping cost in Rupiah
        total = sub_total + defaultShipping;
        refresh_data({
            shipping: defaultShipping,
            sub_total: sub_total,
            total: total,
        });
        return;
    }

    setVisible("#loading_transaction", true);
    setVisible("#transaction", false);
    console.log("jalan dahal");

    $.ajax({
        url: `/shipping/cost/${origin}/${destinationNum}/${quantity}/${courier}`,
        method: "get",
        dataType: "json",
        success: function (data) {
            var city = $("#city").val();
            var province = $("#province").val();
            $("#shipping_address").val(city + ", " + province);
            shipping = data[0]["costs"][0]["cost"][0]["value"];
            total = sub_total + shipping;
            refresh_data({
                shipping: shipping,
                sub_total: sub_total,
                total: total,
            });

            setVisible("#transaction", true);
            setVisible("#loading_transaction", false);
            console.log("end");
        },
        error: function() {
            // If API call fails, use default shipping
            var defaultShipping = 50000;
            total = sub_total + defaultShipping;
            refresh_data({
                shipping: defaultShipping,
                sub_total: sub_total,
                total: total,
            });
            setVisible("#transaction", true);
            setVisible("#loading_transaction", false);
        }
    });
}
