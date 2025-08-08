
<?php
// start.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>People & Fruits</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    section {
      margin-bottom: 30px;
    }
    h2 {
      color: #2c3e50;
    }
  </style>
</head>
<body>

  <h1>People and Fruits App</h1>

  <!-- List All People -->
  <section>
    <h2>All People</h2>
    <button onclick="loadPeople()">Refresh List</button>
    <ul id="peopleList"></ul>
  </section>

  <!-- Create Person -->
  <section>
    <h2>Create New Person</h2>
    <form id="createPersonForm">
      <input type="text" id="firstName" placeholder="First Name" required>
      <input type="text" id="lastName" placeholder="Last Name" required>
      <button type="submit">Create</button>
    </form>
  </section>

  <!-- Add Fruit to Person -->
<section>
  <h2>Add Preferred Fruit to Person</h2>
  <form id="addFruitForm">
    <select id="personSelect" required>
      <option value="">Select Person</option>
    </select>
    <select id="fruitSelect" required>
      <option value="">Select Fruit</option>
    </select>
    <button type="submit">Add Fruit</button>
  </form>
</section>

  <!-- Fruits List -->
  <section>
    <h2>Fruits</h2>
    <button onclick="loadFruits()">Refresh List</button>
    <ul id="fruitList"></ul>
  </section>

  <!-- Create Fruit -->
  <section>
    <h2>Add New Fruit</h2>
    <form id="createFruitForm">
      <input type="text" id="newFruitName" placeholder="Fruit Name" required>
      <button type="submit">Add Fruit</button>
    </form>
  </section>

  <!-- People with Preferred Fruits -->
  <section>
    <h2>People with Preferred Fruits</h2>
    <button onclick="loadPeopleFruits()">Load People-Fruit Table</button>
    <ul id="peopleFruitsList"></ul>
  </section>
  
    <!-- Generate Report -->
  <section>
    <h2>Generate Fruit Preference Report</h2>
    <button onclick="generateReport()">Generate Report</button>
    <p id="reportStatus">No report requested yet.</p>
  </section>


  <script>
  const API_URL = 'http://localhost:8080/api';

function loadPeople() {
  fetch(`${API_URL}/people`)
    .then(res => res.json())
    .then(data => {
      const list = document.getElementById('peopleList');
      list.innerHTML = '';
      const personSelect = document.getElementById('personSelect');
      personSelect.innerHTML = '<option value="">Select Person</option>';

      data.forEach(person => {
        const li = document.createElement('li');
        li.textContent = `#${person.id} - ${person.firstName} ${person.lastName} `;

        const editBtn = document.createElement('button');
        editBtn.textContent = 'Edit';
        editBtn.style.marginLeft = '10px';
        editBtn.onclick = () => {
          const newFirst = prompt("New First Name:", person.firstName);
          const newLast = prompt("New Last Name:", person.lastName);
          if (newFirst && newLast) {
            fetch(`${API_URL}/people/${person.id}`, {
              method: 'PUT',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ firstName: newFirst, lastName: newLast })
            }).then(() => loadPeople());
          }
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Delete';
        deleteBtn.style.marginLeft = '5px';
        deleteBtn.style.color = 'red';
        deleteBtn.onclick = () => {
          if (confirm(`Delete ${person.firstName} ${person.lastName}?`)) {
            fetch(`${API_URL}/people/${person.id}`, {
              method: 'DELETE'
            }).then(() => loadPeople());
          }
        };

        li.appendChild(editBtn);
        li.appendChild(deleteBtn);
        list.appendChild(li);

        const option = document.createElement('option');
        option.value = person.id;
        option.textContent = `${person.firstName} ${person.lastName}`;
        personSelect.appendChild(option);
      });
    });
}
    function loadPeopleFruits() {
    fetch(`${API_URL}/people`)
      .then(res => res.json())
      .then(data => {
        const list = document.getElementById('peopleFruitsList');
        list.innerHTML = '';

        data.forEach(entry => {
          const li = document.createElement('li');

          const fruits = entry.preferredFruits.map(f => f.name).join(', ') || 'None';
          li.textContent = `${entry.firstName} ${entry.lastName} likes: ${fruits}`;
          list.appendChild(li);
        });
      });
  }

  function loadFruits() {
    fetch(`${API_URL}/fruits`)
      .then(res => res.json())
      .then(data => {
        const list = document.getElementById('fruitList');
        list.innerHTML = '';
        const fruitSelect = document.getElementById('fruitSelect');
        fruitSelect.innerHTML = '<option value="">Select Fruit</option>';

        data.forEach(fruit => {
          const li = document.createElement('li');
          li.textContent = fruit.name;
          list.appendChild(li);

          const option = document.createElement('option');
          option.value = fruit.id;
          option.textContent = fruit.name;
          fruitSelect.appendChild(option);
        });
      });
  }

  document.getElementById('createPersonForm').addEventListener('submit', e => {
    e.preventDefault();
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;

    fetch(`${API_URL}/people`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ firstName, lastName })
    })
    .then(() => {
      loadPeople();
      e.target.reset();
    });
  });

  document.getElementById('createFruitForm').addEventListener('submit', e => {
    e.preventDefault();
    const name = document.getElementById('newFruitName').value;

    fetch(`${API_URL}/fruits`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name })
    })
    .then(() => {
      loadFruits();
      e.target.reset();
    });
  });

  document.getElementById('addFruitForm').addEventListener('submit', e => {
    e.preventDefault();
    const personId = document.getElementById('personSelect').value;
    const fruitId = document.getElementById('fruitSelect').value;

    fetch(`${API_URL}/people/${personId}/fruit`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ fruit_id: parseInt(fruitId) })
    })
    .then(() => {
      alert("Fruit added!");
      e.target.reset();
      loadPeople(); // Refresh preferred fruits list
    });
  });
  function generateReport() {
  const statusEl = document.getElementById('reportStatus');
  statusEl.innerText = 'Generating report...';

  fetch(`${API_URL}/generate-report`, { method: 'POST' })
    .then(res => res.json())
    .then(data => {
      const reportId = data.reportId;

      // Poll Redis
      const interval = setInterval(() => {
        fetch(`${API_URL}/report-status/${reportId}`)
          .then(res => res.json())
          .then(statusData => {
            if (statusData.status.startsWith("ready")) {
              clearInterval(interval);
              const filename = statusData.status.split(':')[1];
              statusEl.innerHTML = `✅ Report is ready: <a href="/reports/${filename}" download>Download CSV</a>`;
            } else if (statusData.status === 'error') {
              clearInterval(interval);
              statusEl.innerText = '❌ Error generating report.';
            }
          });
      }, 2000);
    });
}

  // Initial load
  loadPeople();
  loadFruits();

</script>


</body>
</html>
