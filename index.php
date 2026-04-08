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
body { 
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
  background: #f0f2f5; 
  padding: 15px; 
}

.container { 
  max-width: 1000px; 
  margin: auto; 
  background: white; 
  padding: 12px; 
  border-radius: 15px; 
  box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
}

h1 { 
  color: #1a73e8; 
  text-align: center; 
  font-size: 18px;
  margin: 0 0 6px 0;
}

.top-section { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 6px;
  margin-bottom: 6px;
}

.box { 
  background: #fff; 
  padding: 6px; 
  border: 1px solid #e0e0e0; 
  border-radius: 6px; 
}

input, textarea, select { 
  width: 100%; 
  margin-bottom: 6px; 
  padding: 8px; 
  border: 1px solid #ddd; 
  border-radius: 8px; 
  box-sizing: border-box; 
  font-size: 14px;
}

.btn-save { 
  background: #34a853; 
  color: white; 
  border: none; 
  padding: 10px; 
  width: 100%; 
  border-radius: 8px; 
  cursor: pointer; 
  font-weight: bold; 
}

.tab-menu { 
  display: flex; 
  gap: 6px; 
  margin-bottom: 10px; 
  flex-wrap: wrap; 
  border-bottom: 1px solid #ddd; 
  padding-bottom: 6px; 
}

.tab-btn { 
  padding: 6px 14px; 
  cursor: pointer; 
  border: none; 
  background: #e8f0fe; 
  color: #1a73e8; 
  border-radius: 20px; 
  font-weight: 500; 
  font-size: 14px;
}

.prompt-list { 
  display: grid; 
  gap: 12px; 
}

.card { 
  background: white; 
  border: 1px solid #e0e0e0; 
  padding: 15px; 
  border-radius: 10px; 
}

.card-cat { 
  font-size: 11px; 
  color: #70757a; 
  text-transform: uppercase; 
}

.card-title { 
  font-size: 16px; 
  font-weight: bold; 
  margin: 6px 0; 
}

.card-text { 
  background: #f8f9fa; 
  padding: 12px; 
  border-radius: 8px; 
  border: 1px dashed #dadce0; 
  white-space: pre-wrap; 
  margin-bottom: 12px; 
}

.card-btns { 
  display: flex; 
  gap: 10px; 
}

.btn-copy { 
  background: #1a73e8; 
  color: white; 
  border: none; 
  padding: 6px 12px; 
  border-radius: 5px; 
  cursor: pointer; 
}

.btn-edit { 
  background: #5f6368; 
  color: white; 
  padding: 6px 12px; 
  border-radius: 5px; 
  text-decoration: none; 
}

.btn-del { 
  color: #d93025; 
  text-decoration: none; 
}
</style>
</head>

<body>

<div class="container">
<h1>Prompt Library</h1>

<div class="top-section">

<div class="box">
<h3>Quick Search</h3>
<form method="GET">
<input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
</form>
</div>

<div class="box">
<h3>Add New</h3>
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
<button type="submit" name="add_prompt" class="btn-save">Save</button>
</form>
</div>

</div>

<div class="tab-menu">
    <a href="?" class="tab-btn">All</a>
    <a href="?category=Google Ads" class="tab-btn">Google Ads</a>
    <a href="?category=Voice AI" class="tab-btn">Voice AI</a>
    <a href="?category=Landing Page" class="tab-btn">Landing Page</a>
    <a href="?category=APPS" class="tab-btn">APPS</a>
</div>
<div class="prompt-list">

<?php while($row = $result->fetch_assoc()) { ?>

<?php
$fullPrompt = "";

if (!empty($row['role'])) $fullPrompt .= "Role:\n".$row['role']."\n\n";
if (!empty($row['task'])) $fullPrompt .= "Task:\n".$row['task']."\n\n";
if (!empty($row['input'])) $fullPrompt .= "Input:\n".$row['input']."\n\n";
if (!empty($row['constraints'])) $fullPrompt .= "Constraints:\n".$row['constraints']."\n\n";
if (!empty($row['output_format'])) $fullPrompt .= "Output Format:\n".$row['output_format']."\n\n";
?>

<div class="card">

<span class="card-cat"><?php echo $row['category']; ?></span>

<div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>

<div class="card-text">
<?php echo htmlspecialchars($row['prompt_text']); ?>
</div>

<textarea id="full<?php echo $row['id']; ?>" style="display:none; width:100%;" readonly>
<?php echo htmlspecialchars($fullPrompt); ?>
</textarea>

<div class="card-btns">
<button class="btn-copy" onclick="copyText('full<?php echo $row['id']; ?>')">Copy Prompt</button>
<button class="btn-copy" onclick="toggleView(<?php echo $row['id']; ?>, this)">View</button>
<a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
<a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete forever?')">Delete</a>
</div>

</div>

<?php } ?>

</div>

</div>

<script>
function toggleView(id, btn) {
    var el = document.getElementById('full' + id);

    if (el.style.display === "none") {
        el.style.display = "block";
        el.style.height = "auto";
        el.style.height = el.scrollHeight + "px";
        btn.innerText = "Hide";
    } else {
        el.style.display = "none";
        btn.innerText = "View";
    }
}

function copyText(id) {
    var el = document.getElementById(id);
    navigator.clipboard.writeText(el.value);
    alert("Copied!");
}
</script>

</body>
</html>
