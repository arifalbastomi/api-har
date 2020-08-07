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
            width: <?php echo $w ?>px;
			height: <?php echo $h ?>px;
        }
		html {
		
		 overflow:hidden;
		 -ms-overflow-style: none;
			border:0px;
		}
    </style>
</head>
<body>
    <table class="table">
        <tr>
            <td class="text-center">
                <img class="qrcode"
                    src="<?php echo $url; ?>">
            </td>
        </tr>
    </table>
</body>
</html>