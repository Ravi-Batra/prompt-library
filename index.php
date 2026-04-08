<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// FILTER
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
body { font-family: 'Segoe UI'; background:#f0f2f5; padding:15px; }

.container { max-width:1000px; margin:auto; background:white; padding:12px; border-radius:15px; box-shadow:0 4px 20px rgba(0,0,0,0.08); }

h1 { text-align:center; color:#1a73e8; font-size:18px; }

.top-section { display:grid; grid-template-columns:1fr 1fr; gap:6px; }

.box { padding:8px; border:1px solid #ddd; border-radius:6px; }

input, textarea, select { width:100%; margin-bottom:6px; padding:8px; border-radius:8px; border:1px solid #ddd; }

.btn-save { background:#34a853; color:white; padding:10px; border:none; width:100%; border-radius:8px; }

.tab-menu { display:flex; gap:6px; margin:10px 0; flex-wrap:wrap; }

.tab-btn { padding:6px 12px; border-radius:20px; background:#e8f0fe; color:#1a73e8; text-decoration:none; }

.tab-btn.active { background:#1a73e8; color:white; }

.card { border:1px solid #ddd; padding:15px; border-radius:10px; margin-bottom:12px; }

.card-cat { font-size:11px; color:#777; text-transform:uppercase; }

.card-title { font-weight:bold; margin:6px 0; }

.card-text { background:#f8f9fa; padding:10px; border-radius:8px; border:1px dashed #ddd; margin-bottom:10px; }

.card-btns { display:flex; gap:10px; }

.btn-copy { background:#1a73e8; color:white; border:none; padding:6px 10px; border-radius:5px; }

.btn-edit { background:#5f6368; color:white; padding:6px 10px; border-radius:5px; text-decoration:none; }

.btn-del { color:red; text-decoration:none; }

.full-view { display:none; background:#f1f3f4; padding:10px; border-radius:8px; margin-top:10px; white-space:pre-wrap; }
</style>
</head>

<body>

<div class="container">
<h1>Prompt Library</h1>

<div class="top-section">

<div class="box">
<h3>Quick Search</h3>
<form method="GET">
<input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
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
<button name="add_prompt" class="btn-save">Save</button>
</form>
</div>

</div>

<!-- Tabs -->
<div class="tab-menu">
<?php
$tabs = ["All"=>"", "Google Ads"=>"Google Ads", "Voice AI"=>"Voice AI", "Landing Page"=>"Landing Page", "APPS"=>"APPS"];
foreach($tabs as $label=>$val){
    $active = ($category == $val || ($val=="" && $category=="")) ? "active" : "";
    $link = $val=="" ? "?" : "?category=".urlencode($val);
    echo "<a href='$link' class='tab-btn $active'>$label</a>";
}
?>
</div>

<?php while($row = $result->fetch_assoc()) { ?>

<?php
$fullPrompt = "";
if ($row['role']) $fullPrompt .= "Role:\n".$row['role']."\n\n";
if ($row['task']) $fullPrompt .= "Task:\n".$row['task']."\n\n";
if ($row['input']) $fullPrompt .= "Input:\n".$row['input']."\n\n";
if ($row['constraints']) $fullPrompt .= "Constraints:\n".$row['constraints']."\n\n";
if ($row['output_format']) $fullPrompt .= "Output Format:\n".$row['output_format'];
?>

<div class="card">

<span class="card-cat"><?php echo $row['category']; ?></span>

<div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>

<div class="card-text"><?php echo htmlspecialchars($row['prompt_text']); ?></div>

<div class="full-view" id="view<?php echo $row['id']; ?>">
<?php echo htmlspecialchars($fullPrompt); ?>
</div>

<div class="card-btns">
<button class="btn-copy" onclick="copyFull(<?php echo $row['id']; ?>)">Copy</button>
<button class="btn-copy" onclick="toggleView(<?php echo $row['id']; ?>, this)">View</button>
<a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
<a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete forever?')">Delete</a>
</div>

</div>

<?php } ?>

</div>

<script>
function toggleView(id, btn){
    let el = document.getElementById('view'+id);
    if(el.style.display==="block"){
        el.style.display="none";
        btn.innerText="View";
    } else {
        el.style.display="block";
        btn.innerText="Hide";
    }
}

function copyFull(id){
    let text = document.getElementById('view'+id).innerText;
    navigator.clipboard.writeText(text);
    alert("Copied full prompt!");
}
</script>

</body>
</html>
