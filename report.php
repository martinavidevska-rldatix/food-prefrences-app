<?php
// report.php
?>

<form id="report-form">
    <button type="submit">Generate Report</button>
</form>
<p id="status"></p>
<a id="download-link" style="display: none;" href="#" download>Download Report</a>
<script>
document.getElementById('report-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    document.getElementById('status').innerText = "Your report is being generated...";
    document.getElementById('download-link').style.display = "none";

    const res = await fetch('/report/generate', { method: 'POST' });
    const { report_id } = await res.json();

    const interval = setInterval(async () => {
        const statusRes = await fetch(`/report/status?report_id=${report_id}`);
        const { ready } = await statusRes.json();

        if (ready) {
            clearInterval(interval);
            document.getElementById('status').innerText = "Your report is generated. Get it from here:";
            const link = document.getElementById('download-link');
            link.href = `/report/download/${report_id}`;
            link.style.display = "inline";
        }
    }, 3000); 
});
//
// document.getElementById('report-form1').addEventListener('submit', async (e) => {
//     e.preventDefault();
//     document.getElementById('status').innerText = "Your report is being generated...";
//     document.getElementById('download-link1').style.display = "none";
//
//     const res = await fetch('/report/generate1', { method: 'POST' });
//     const { report_id } = await res.json();
//
//     const interval = setInterval(async () => {
//         const statusRes = await fetch(`/report/status?report_id=${report_id}`);
//         const { ready } = await statusRes.json();
//
//         if (ready) {
//             clearInterval(interval);
//             document.getElementById('status').innerText = "Your report is generated. Get it from here:";
//             const link = document.getElementById('download-link');
//             link.href = `/report/download/${report_id}`;
//             link.style.display = "inline";
//         }
//     }, 3000);
// });
</script>
