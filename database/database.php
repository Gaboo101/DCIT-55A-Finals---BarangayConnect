<?php

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "barangayconnect";

function connectDB() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    if (!$conn) {
        return null;
    }

    return $conn;
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Generate unique tracking code
function generateTrackingCode() {
    $prefix = "BC";
    $year = date('Y');
    $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5));
    return $prefix . "-" . $year . "-" . $random;
}

// Get all document types
function getAllDocumentTypes($conn) {
    $result = $conn->query("SELECT * FROM document_types ORDER BY name ASC");
    $documents = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
    }
    return $documents;
}

// Add new document type
function addDocumentType($conn, $name, $fee, $description) {
    $fee = floatval($fee);
    $stmt = $conn->prepare("INSERT INTO document_types (name, fee, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $fee, $description);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Update document type
function updateDocumentType($conn, $id, $name, $fee, $description) {
    $fee = floatval($fee);
    $stmt = $conn->prepare("UPDATE document_types SET name = ?, fee = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $name, $fee, $description, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Update document status (Active/Inactive)
function updateDocumentStatus($conn, $id, $status) {
    $stmt = $conn->prepare("UPDATE document_types SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Delete document type
function deleteDocumentType($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM document_types WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Get all requests with filters
function getAllRequests($conn, $status = null) {
    if ($status) {
        $stmt = $conn->prepare("SELECT r.*, d.name as doc_name, u.first_name, u.last_name, u.email FROM requests r 
                               JOIN document_types d ON r.doc_id = d.id 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.status = ? ORDER BY r.request_date DESC");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT r.*, d.name as doc_name, u.first_name, u.last_name, u.email FROM requests r 
                              JOIN document_types d ON r.doc_id = d.id 
                              JOIN users u ON r.user_id = u.id 
                              ORDER BY r.request_date DESC");
    }
    
    $requests = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    return $requests;
}

// Get single request by tracking code
function getRequestByTrackingCode($conn, $code) {
    $stmt = $conn->prepare("SELECT r.*, d.name as doc_name, u.first_name, u.last_name FROM requests r 
                           JOIN document_types d ON r.doc_id = d.id 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.tracking_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Get all requests by user ID
function getRequestsByUserId($conn, $user_id) {
    $stmt = $conn->prepare("SELECT r.*, d.name as doc_name FROM requests r 
                           JOIN document_types d ON r.doc_id = d.id 
                           WHERE r.user_id = ? 
                           ORDER BY r.request_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    return $requests;
}

// Update request status
function updateRequestStatus($conn, $request_id, $status, $remarks = null) {
    $stmt = $conn->prepare("UPDATE requests SET status = ?, admin_remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $remarks, $request_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Add new request
function addRequest($conn, $user_id, $doc_id, $purpose, $id_photo_path = null) {
    $tracking_code = generateTrackingCode();
    $stmt = $conn->prepare("INSERT INTO requests (user_id, doc_id, tracking_code, purpose, id_photo_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $doc_id, $tracking_code, $purpose, $id_photo_path);
    $result = $stmt->execute();
    $stmt->close();
    return $result ? $tracking_code : false;
}

// Get all residents
function getAllResidents($conn) {
    $result = $conn->query("SELECT * FROM users WHERE role = 'Resident' ORDER BY created_at DESC");
    $residents = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $residents[] = $row;
        }
    }
    return $residents;
}

// Get document type by ID
function getDocumentTypeById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM document_types WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}


