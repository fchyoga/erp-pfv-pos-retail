<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode - {{ $product->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f0f0f0;
        }
        .label-container {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .product-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .product-sku {
            font-size: 12px;
            color: #555;
            margin-bottom: 15px;
        }
        .price {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        @media print {
            body {
                background: white;
                height: auto;
                align-items: flex-start;
            }
            .label-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            @page {
                size: auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body>
    <div class="label-container">
        <div class="product-name">{{ $product->name }}</div>
        <div class="product-sku">SKU: {{ $product->sku }}</div>
        <svg id="barcode"></svg>
        <div class="price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $product->barcode ?? $product->sku }}", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 50,
            displayValue: true
        });

        // Automatically trigger print dialog
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
