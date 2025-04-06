<?php
// generate.php

session_start();
include 'config.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    die("Access denied.");
}

$doc_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get document details
$stmt = $conn->prepare("SELECT document_type, purpose, request_date FROM document_requests WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$stmt->bind_result($document_type, $purpose, $request_date);
$stmt->fetch();
$stmt->close();

// Get user name
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

function getDocumentBody($document_type, $user_name, $purpose, $request_date) {
    $formatted_date = date("F j, Y", strtotime($request_date));

    switch ($document_type) {
        case 'Barangay Clearance':
            return "
                This is to certify that Mr./Ms. <strong>$user_name</strong> is a bona fide resident of Barangay Camagong, Nasipit, Agusan del Norte, and has not been involved in any unlawful or immoral activities based on the records of this barangay.
                <br><br>This Barangay Clearance is hereby issued upon the request of the aforementioned individual for the purpose of <strong>$purpose</strong> on this day, $formatted_date.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";

        case 'Certificate of Residency':
            return "
                This is to formally certify that <strong>$user_name</strong> is a legitimate resident of Barangay Camagong, Nasipit, Agusan del Norte, and has been residing at this address for a considerable period of time.
                <br><br>This certificate is issued upon the request of the above-named individual for the purpose of <strong>$purpose</strong>.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";

        case 'Certificate of Indigency':
            return "
                This is to certify that <strong>$user_name</strong> is an indigent resident of Barangay Camagong, Nasipit, Agusan del Norte, with limited financial resources.
                <br><br>This certificate is issued upon the request of the aforementioned individual to support the purpose of <strong>$purpose</strong>.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";

        case 'Certificate of Good Moral Character':
            return "
                This is to certify that <strong>$user_name</strong> is of good moral character and has not been involved in any criminal or immoral activities, based on the official records of this barangay.
                <br><br>This certification is issued upon the request of the said individual for the purpose of <strong>$purpose</strong>.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";

      
        case 'Barangay Protection Order':
            return "
                This is to certify that <strong>$user_name</strong> is a recipient of a Barangay Protection Order (BPO) issued under the provisions of Republic Act No. 9262, otherwise known as the Anti-Violence Against Women and Their Children Act of 2004.
                <br><br>The protection order was issued on $formatted_date upon the request of the concerned party, for the purpose of <strong>$purpose</strong>.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";

        case 'Barangay Business Permit':
            return "
                This is to certify that <strong>$user_name</strong> is duly authorized to operate a business within the jurisdiction of Barangay Camagong, Nasipit, Agusan del Norte, subject to the existing rules and regulations of the barangay.
                <br><br>This business permit is issued for the purpose of <strong>$purpose</strong> and is valid as of $formatted_date.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";

        default:
            return "
                This is to certify that <strong>$user_name</strong> has requested a copy of the document titled <strong>$document_type</strong> on $formatted_date for the purpose of <strong>$purpose</strong>.
                <br><br>Issued this $formatted_date at Barangay Camagong, Nasipit, Agusan del Norte.
            ";
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($document_type); ?> - Barangay Camagong</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            background-color: #f9f9f9;
            padding: 50px;
            text-align: center;
        }

        .document-box {
            width: 700px;
            margin: 0 auto;
            padding: 40px;
            border: 1px solid #444;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2, h3 {
            margin: 5px 0;
        }

        .header {
            margin-bottom: 30px;
        }

        .body {
            text-align: left;
            font-size: 18px;
            line-height: 1.8;
        }

        .signature {
            margin-top: 60px;
            text-align: left;
        }

        .signature span {
            display: inline-block;
            margin-top: 40px;
        }

        button {
            margin-top: 40px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="document-box">
        <div class="header">
            <h3>Republic of the Philippines</h3>
            <h2>Barangay Camagong</h2>
            <h3>Nasipit, Agusan del Norte</h3>
            <hr>
            <h3><u><?php echo strtoupper(htmlspecialchars($document_type)); ?></u></h3>
        </div>
        <div class="body">
            <p>
                <?php echo getDocumentBody($document_type, htmlspecialchars($user_name), strtoupper(htmlspecialchars($purpose)), $request_date); ?>
            </p>
        </div>

        <div class="signature">
            <p>Signed:</p>
            <span>__________________________<br><em>Punong Barangay</em></span>
        </div>

        <button onclick="window.print()">🖨️ Print Document</button>
    </div>
</body>
</html>
