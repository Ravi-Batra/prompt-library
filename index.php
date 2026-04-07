<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
$host = 'localhost';
$db   = 'u465223560_promptdb';
$user = 'u465223560_promptdb';
$pass = 'Scraplead123';

$conn = new mysqli($host, $user, $pass, $db);

// SAVE
if (isset($_POST['add_prompt'])) {
    $category = $conn->real_escape_string($_POST['category']);
    $title = $conn->real_escape_string($_POST['title']);
    $text = $conn->real_escape_string($_POST['prompt_text']);
    $conn->query("INSERT INTO my_prompts (category, title, prompt_text) VALUES ('$category', '$title', '$text')");
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM my_prompts WHERE id=$id");
    header("Location: index.php");
    exit;
}

// FETCH
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT * FROM my_prompts WHERE 1";

if ($search != '') {
    $query .= " AND (title LIKE '%$search%' OR category LIKE '%$search%' OR prompt_text LIKE '%$search%')";
}

if ($category != '') {
    $query .= " AND TRIM(LOWER(category)) = TRIM(LOWER('$category'))";
}

$query .= " ORDER BY id DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Prompt Library</title>

<style>
body { font-family: Arial; background:#f5f5f5; padding:20px; }
.container { max-width:1000px; margin:auto; background:white; padding:20px; border-radius:10px; }

.card { border:1px solid #ddd; padding:15px; margin-bottom:15px; border-radius:8px; }
.card-title { font-weight:bold; font-size:16px; margin-bottom:5px; }
.card-text { background:#f9f9f9; padding:10px; border-radius:5px; margin-bottom:10px; }

textarea { width:100%; border-radius:6px; padding:10px; }

button { padding:6px 10px; border:none; border-radius:5px; cursor:pointer; }
.btn-copy { background:#1a73e8; color:white; }
.btn-edit { background:#5f6368; color:white; text-decoration:none; padding:6px 10px; border-radius:5px; }
.btn-del { color:red; text-decoration:none; margin-left:10px; }

</style>
</head>

<body>

<div class="container">
<h2>Prompt Library</h2>

<!-- ADD NEW -->
<form method="POST">
<select name="category">
<option>Google Ads</option>
<option>Voice AI</option>
<option>Landing Page</option>
<option>APPS</option>
<option>Client Setup</option>
</select>

<input type="text" name="title" placeholder="Title" required>
<textarea name="prompt_text" placeholder="Short description"></textarea>
<button type="submit" name="add_prompt">Save</button>
</form>

<hr>

<?php while($row = $result->fetch_assoc()) { ?>

<?php
$fullPrompt = "";

if (!empty($row['role'])) {
    $fullPrompt .= "Role:\n" . $row['role'] . "\n\n";
}
if (!empty($row['task'])) {
    $fullPrompt .= "Task:\n" . $row['task'] . "\n\n";
}
if (!empty($row['input'])) {
    $fullPrompt .= "Input:\n" . $row['input'] . "\n\n";
}
if (!empty($row['constraints'])) {
    $fullPrompt .= "Constraints:\n" . $row['constraints'] . "\n\n";
}
if (!empty($row['output_format'])) {
    $fullPrompt .= "Output Format:\n" . $row['output_format'] . "\n\n";
}
?>

<div class="card">

<div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>

<div class="card-text">
<?php echo htmlspecialchars($row['prompt_text']); ?>
</div>

<textarea id="view<?php echo $row['id']; ?>" style="display:none; height:180px;" readonly><?php echo htmlspecialchars($fullPrompt); ?></textarea>

<button class="btn-copy" onclick="copyText('view<?php echo $row['id']; ?>')">Copy Prompt</button>

<button class="btn-copy" onclick="toggleView(<?php echo $row['id']; ?>, this)">View</button>

<a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>

<a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete?')">Delete</a>

</div>

<?php } ?>

</div>

<script>
function toggleView(id, btn) {
    var el = document.getElementById('view' + id);

    if (el.style.display === "none") {
        el.style.display = "block";
        btn.innerText = "Hide";
    } else {
        el.style.display = "none";
        btn.innerText = "View";
    }
}

function copyText(id) {
    var el = document.getElementById(id);
    navigator.clipboard.writeText(el.value);
    alert("Copied full prompt!");
}
</script>

</body>
</html>
