<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 28px;
        }
        .content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .content p {
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Libri Usati Crema</h1>
        </div>
        <div class="content">
            <p>Buongiorno,</p>
            <p>hai ricevuto questa mail perché è stata richiesta la creazione o il reset della password per il tuo account.</p>
            <p>Clicca il pulsante qui sotto per impostare la tua password:</p>
            <a href="{{ $link }}" class="button">Imposta Password</a>
            <p> Se il pulsante non funziona, copia e incolla questo link nel tuo browser:</p>
            <p><a href="{{ $link }}">{{ $link }}</a></p>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                Se non hai richiesto questo, ignora questo messaggio. Il link scade dopo 24 ore.
            </p>
        </div>
        <div class="footer">
            <p><a href="libriusaticrema.it">Libri Usati Crema</a></p>
        </div>
    </div>
</body>
</html>
