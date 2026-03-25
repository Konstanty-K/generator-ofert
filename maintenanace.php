<?php
// Ten kod sprawdza, czy tryb konserwacji jest włączony
if (getenv('MAINTENANCE_MODE') === 'true'):
    ?>
    <style>
        /* "Wyszarzenie" i zablokowanie strony */
        #maintenance-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7); /* Ciemne, półprzezroczyste tło */
            backdrop-filter: blur(5px);    /* Rozmycie tego, co pod spodem */
            z-index: 999999;               /* Musi być na samym wierzchu */
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            cursor: not-allowed;
        }
        .msg-box {
            background: #222;
            padding: 40px;
            border-radius: 15px;
            border: 2px solid #444;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        /* Blokada interakcji z resztą strony */
        body {
            overflow: hidden !important; /* Blokuje przewijanie */
        }
    </style>

    <div id="maintenance-overlay">
        <div class="msg-box">
            <h1>Strona w budowie 🛠️</h1>
            <p>Wprowadzamy ulepszenia. Wrócimy niebawem!</p>
        </div>
    </div>

<?php
// Możesz tu dodać exit(); jeśli chcesz całkowicie zatrzymać ładowanie reszty strony
// exit();
endif;
?>