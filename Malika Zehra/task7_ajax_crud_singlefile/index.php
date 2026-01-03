<?php
// ==============================
// SINGLE-FILE AJAX CRUD (PHP + MySQL)
// File: index.php
// ==============================

// ---------- DB CONFIG ----------
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";          // change if your MySQL has a password
$DB_NAME = "ajax_crud"; // must match db.sql

// ---------- CONNECT ----------
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
  // If API call expects JSON, return JSON error; otherwise show plain text.
  if (isset($_GET["action"])) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(500);
    echo json_encode(["success"=>false,"message"=>"DB connection failed: ".$conn->connect_error]);
    exit;
  }
  die("DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ---------- HELPERS ----------
function input_json() {
  $raw = file_get_contents("php://input");
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function respond($arr, $code = 200) {
  header("Content-Type: application/json; charset=utf-8");
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

// ==============================
// API ENDPOINTS (AJAX)
// URL examples:
//   index.php?action=read
//   index.php?action=create
//   index.php?action=update
//   index.php?action=delete
// ==============================
$action = $_GET["action"] ?? "";

if ($action === "read") {
  $res = $conn->query("SELECT id, first_name, last_name, email, created_at FROM students ORDER BY id DESC");
  $rows = [];
  if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
  }
  respond(["success"=>true,"data"=>$rows]);
}

if ($action === "create") {
  $d = input_json();
  $first = trim($d["first_name"] ?? "");
  $last  = trim($d["last_name"] ?? "");
  $email = trim($d["email"] ?? "");

  if ($first === "" || $last === "" || $email === "") {
    respond(["success"=>false,"message"=>"All fields are required"], 400);
  }

  $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, email) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $first, $last, $email);

  if (!$stmt->execute()) {
    respond(["success"=>false,"message"=>"Insert failed (duplicate email or DB error)."], 400);
  }
  respond(["success"=>true,"message"=>"Record inserted"]);
}

if ($action === "update") {
  $d = input_json();
  $id    = intval($d["id"] ?? 0);
  $first = trim($d["first_name"] ?? "");
  $last  = trim($d["last_name"] ?? "");
  $email = trim($d["email"] ?? "");

  if ($id <= 0 || $first === "" || $last === "" || $email === "") {
    respond(["success"=>false,"message"=>"Valid id and all fields are required"], 400);
  }

  $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, email=? WHERE id=?");
  $stmt->bind_param("sssi", $first, $last, $email, $id);

  if (!$stmt->execute()) {
    respond(["success"=>false,"message"=>"Update failed (duplicate email or DB error)."], 400);
  }
  respond(["success"=>true,"message"=>"Record updated"]);
}

if ($action === "delete") {
  $d = input_json();
  $id = intval($d["id"] ?? 0);

  if ($id <= 0) {
    respond(["success"=>false,"message"=>"Valid id is required"], 400);
  }

  $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
  $stmt->bind_param("i", $id);

  if (!$stmt->execute()) {
    respond(["success"=>false,"message"=>"Delete failed"], 400);
  }
  respond(["success"=>true,"message"=>"Record deleted"]);
}

// If no action => show the HTML page below.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AJAX CRUD (Single File: PHP + MySQL)</title>
  <style>
    *{box-sizing:border-box}
    body{font-family:Arial, sans-serif;background:#f4f4f4;margin:0;padding:20px}
    .wrap{max-width:900px;margin:auto}
    .card{background:#fff;border:1px solid #ddd;border-radius:10px;padding:16px;margin-bottom:16px}
    h2{margin:0 0 12px}
    .row{display:flex;gap:12px;flex-wrap:wrap}
    .field{flex:1;min-width:200px}
    label{display:block;margin:8px 0 6px}
    input{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px}
    button{padding:10px 14px;border:0;border-radius:8px;cursor:pointer}
    .btn-primary{background:#2563eb;color:#fff}
    .btn-secondary{background:#6b7280;color:#fff}
    .btn-danger{background:#dc2626;color:#fff}
    .btn-warning{background:#f59e0b;color:#111}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #ddd;padding:10px;text-align:left}
    th{background:#f2f2f2}
    .msg{margin-top:10px;padding:10px;border-radius:8px;display:none}
    .msg.ok{background:#dcfce7;border:1px solid #86efac}
    .msg.err{background:#fee2e2;border:1px solid #fca5a5}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .small{font-size:12px;color:#6b7280;margin-top:8px}
  </style>
</head>
<body>
  <div class="wrap">

    <div class="card">
      <h2>Student Form (Create / Update)</h2>

      <input type="hidden" id="id" />

      <div class="row">
        <div class="field">
          <label>First Name</label>
          <input type="text" id="first_name" placeholder="Enter first name">
        </div>
        <div class="field">
          <label>Last Name</label>
          <input type="text" id="last_name" placeholder="Enter last name">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" id="email" placeholder="Enter email">
        </div>
      </div>

      <div class="row" style="margin-top:12px;">
        <button class="btn-primary" id="saveBtn">Save (Create)</button>
        <button class="btn-warning" id="updateBtn" style="display:none;">Update</button>
        <button class="btn-secondary" id="resetBtn">Reset</button>
      </div>

      <div class="msg" id="msgBox"></div>
      <div class="small">Note: Email is UNIQUE. Duplicate email will fail.</div>
    </div>

    <div class="card">
      <h2>Records (Read)</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>First</th>
            <th>Last</th>
            <th>Email</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>

  </div>

<script>
  // IMPORTANT: since API is in the same file, use "index.php"
  const api = "index.php";

  const idEl = document.getElementById("id");
  const firstEl = document.getElementById("first_name");
  const lastEl  = document.getElementById("last_name");
  const emailEl = document.getElementById("email");

  const saveBtn = document.getElementById("saveBtn");
  const updateBtn = document.getElementById("updateBtn");
  const resetBtn = document.getElementById("resetBtn");

  const tbody = document.getElementById("tbody");
  const msgBox = document.getElementById("msgBox");

  function showMsg(text, ok=true){
    msgBox.style.display = "block";
    msgBox.className = "msg " + (ok ? "ok" : "err");
    msgBox.textContent = text;
  }

  function resetForm(){
    idEl.value = "";
    firstEl.value = "";
    lastEl.value  = "";
    emailEl.value = "";
    saveBtn.style.display = "inline-block";
    updateBtn.style.display = "none";
    msgBox.style.display = "none";
  }

  async function readData(){
    try{
      const res = await fetch(`${api}?action=read`);
      const data = await res.json();

      if(!res.ok || !data.success){
        showMsg(data.message || "Read failed", false);
        return;
      }

      tbody.innerHTML = "";
      data.data.forEach(row => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${row.id}</td>
          <td>${row.first_name}</td>
          <td>${row.last_name}</td>
          <td>${row.email}</td>
          <td>${row.created_at}</td>
          <td>
            <div class="actions">
              <button class="btn-warning" onclick='fillForm(${JSON.stringify(row)})'>Edit</button>
              <button class="btn-danger" onclick="deleteRow(${row.id})">Delete</button>
            </div>
          </td>
        `;
        tbody.appendChild(tr);
      });
    } catch(err){
      showMsg("API error: " + err.message, false);
    }
  }

  window.fillForm = function(row){
    idEl.value = row.id;
    firstEl.value = row.first_name;
    lastEl.value  = row.last_name;
    emailEl.value = row.email;

    saveBtn.style.display = "none";
    updateBtn.style.display = "inline-block";
  }

  async function createRow(){
    try{
      const payload = {
        first_name: firstEl.value.trim(),
        last_name: lastEl.value.trim(),
        email: emailEl.value.trim()
      };

      const res = await fetch(`${api}?action=create`, {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if(!res.ok || !data.success){
        showMsg(data.message || "Insert failed", false);
        return;
      }

      showMsg(data.message || "Record inserted", true);
      resetForm();
      readData();
    } catch(err){
      showMsg("API error: " + err.message, false);
    }
  }

  async function updateRow(){
    try{
      const payload = {
        id: Number(idEl.value),
        first_name: firstEl.value.trim(),
        last_name: lastEl.value.trim(),
        email: emailEl.value.trim()
      };

      const res = await fetch(`${api}?action=update`, {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if(!res.ok || !data.success){
        showMsg(data.message || "Update failed", false);
        return;
      }

      showMsg(data.message || "Record updated", true);
      resetForm();
      readData();
    } catch(err){
      showMsg("API error: " + err.message, false);
    }
  }

  window.deleteRow = async function(id){
    if(!confirm("Delete this record?")) return;

    try{
      const res = await fetch(`${api}?action=delete`, {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id})
      });

      const data = await res.json();
      if(!res.ok || !data.success){
        showMsg(data.message || "Delete failed", false);
        return;
      }

      showMsg(data.message || "Record deleted", true);
      readData();
    } catch(err){
      showMsg("API error: " + err.message, false);
    }
  }

  saveBtn.addEventListener("click", (e) => { e.preventDefault(); createRow(); });
  updateBtn.addEventListener("click", (e) => { e.preventDefault(); updateRow(); });
  resetBtn.addEventListener("click", (e) => { e.preventDefault(); resetForm(); });

  readData();
</script>
</body>
</html>
