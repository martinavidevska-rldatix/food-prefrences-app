<!-- src/views/person/list.php -->
<!DOCTYPE html>
<html>
<head>
    <title>People List</title>
</head>
<body>
    <h1>All People</h1>

    <a href="/person/new">Add New Person</a>

    <ul>
        <?php foreach ($people as $person): ?>
            <li>
                <?= htmlspecialchars($person->getFirstName() . ' ' . $person->getLastName()) ?>
                <a href="/person/show?id=<?= $person->getId() ?>">View</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
