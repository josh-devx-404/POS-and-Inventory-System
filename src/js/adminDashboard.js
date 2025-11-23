function toggleTheme() {
  const body = document.body;
  const toggle = document.querySelector(".toggle-switch");

  body.classList.toggle("dark-mode");
  body.classList.toggle("light-mode");
  toggle.classList.toggle("active");
}

// Daily Sales Chart
const salesCtx = document.getElementById("salesChart").getContext("2d");
new Chart(salesCtx, {
  type: "line",
  data: {
    labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    datasets: [
      {
        label: "Sales (₱)",
        data: [1200, 1900, 1700, 2200, 2800, 3500, 2900],
        borderWidth: 3,
        fill: false,
        borderColor: "#d4a574",
        tension: 0.4,
      },
    ],
  },
});

const productCtx = document.getElementById("productsChart").getContext("2d");
new Chart(productCtx, {
  type: "bar",
  data: {
    labels: ["Latte", "Cappuccino", "Americano", "Sandwich", "Muffin"],
    datasets: [
      {
        label: "Orders",
        data: [180, 150, 130, 110, 95],
        backgroundColor: [
          "#d4a574",
          "#b87f4f",
          "#8d5a33",
          "#c79a6b",
          "#6a4428",
        ],
      },
    ],
  },
});
