<?php
include '../backend/security.php'; // Links head  
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Responsive Product Gallery</title>
    <style>
      /* Reset & basics */
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      }

      body {
        background: #f4f4f9;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }

      /* Navigation Bar */
      nav {
        background: #4a90e2;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        flex-wrap: wrap;
      }

      nav .logo {
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: 2px;
      }

      nav ul {
        list-style: none;
        display: flex;
        gap: 20px;
      }

      nav ul li {
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 6px;
        transition: background-color 0.3s ease;
      }

      nav ul li:hover,
      nav ul li.active {
        background-color: rgba(255, 255, 255, 0.25);
      }

      /* Main content container */
      main {
        flex: 1;
        padding: 20px 30px;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
      }

      /* Grid container for products */
      .product-grid {
        display: grid;
        gap: 20px;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      }

      /* Product card */
      .product-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease;
      }

      .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgb(0 0 0 / 0.15);
      }

      .product-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-bottom: 1px solid #ddd;
      }

      .product-info {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }

      .product-info h3 {
        font-size: 1.1rem;
        margin-bottom: 8px;
        color: #333;
      }

      .product-info .price {
        font-weight: 700;
        font-size: 1rem;
        color: #4a90e2;
      }

      /* Responsive nav for small screens */
      @media (max-width: 600px) {
        nav {
          justify-content: center;
        }

        nav ul {
          width: 100%;
          justify-content: space-around;
          margin-top: 10px;
        }
      }
    </style>
  </head>
  <body>
    <nav>
      <div class="logo">FlowerShop</div>
      <ul>
        <li class="active">Home</li>
        <li>Products</li>
        <li>About</li>
        <li>Contact</li>
      </ul>
    </nav>

    <main>
      <div class="product-grid">
        <div class="product-card">
          <img
            src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80"
            alt="Rose Bouquet"
          />
          <div class="product-info">
            <h3>Rose Bouquet</h3>
            <div class="price">$25.00</div>
          </div>
        </div>
        <div class="product-card">
          <img
            src="https://images.unsplash.com/photo-1465188035480-9df1b2d57f49?auto=format&fit=crop&w=400&q=80"
            alt="Sunflower Pot"
          />
          <div class="product-info">
            <h3>Sunflower Pot</h3>
            <div class="price">$18.00</div>
          </div>
        </div>
        <div class="product-card">
          <img
            src="https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?auto=format&fit=crop&w=400&q=80"
            alt="Lavender"
          />
          <div class="product-info">
            <h3>Lavender</h3>
            <div class="price">$15.00</div>
          </div>
        </div>
        <div class="product-card">
          <img
            src="https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=400&q=80"
            alt="Orchid"
          />
          <div class="product-info">
            <h3>Orchid</h3>
            <div class="price">$30.00</div>
          </div>
        </div>
        <div class="product-card">
          <img
            src="https://images.unsplash.com/photo-1486308510493-cb8950f9ae66?auto=format&fit=crop&w=400&q=80"
            alt="Daisy"
          />
          <div class="product-info">
            <h3>Daisy</h3>
            <div class="price">$12.00</div>
          </div>
        </div>
        <div class="product-card">
          <img
            src="https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=400&q=80"
            alt="Tulip"
          />
          <div class="product-info">
            <h3>Tulip</h3>
            <div class="price">$20.00</div>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
