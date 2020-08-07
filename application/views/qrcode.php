<html>
<head>
    <style>
        .text-center {
            text-align: center;
        }

        .table {
            width: 100%;
            height: 100%;
        }

        .qrcode {
            width: 300px;
        }
    </style>
</head>
<body>
    <table class="table">
        <tr>
            <td class="text-center">
                <img class="qrcode"
					<?php 
						$rand=rand(10000,99999);
					?>
                    src="<?php echo $url_qr."?dummy=".$rand; ?>">
            </td>
        </tr>
    </table>
</body>
</html>