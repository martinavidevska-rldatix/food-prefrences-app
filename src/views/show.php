<!-- src/views/person/show.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Person Details</title>
</head>
<body>
    <h1>Person Details</h1>

    <p><strong>Full Name:</strong> <?= htmlspecialchars($person->getFirstName() . ' ' . $person->getLastName()) ?></p>

    <h2>Preferred Fruits</h2>
    <ul>
        <?php foreach ($person->getPreferredFruits() as $fruit): ?>
            <li><?= htmlspecialchars($fruit->getName()) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Add Preferred Fruit</h3>
    <form method="POST" action="/person/add-fruit">
        <input type="hidden" name="person_id" value="<?= $person->getId() ?>" />
        <select name="fruit_id">
            <?php foreach ($fruits as $fruit): ?>
                <option value="<?= $fruit->getId() ?>">
                    <?= htmlspecialchars($fruit->getName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Add Fruit</button>
    </form>

    <br>
    <a href="/person/list">Back to List</a>
</body>
</html>
