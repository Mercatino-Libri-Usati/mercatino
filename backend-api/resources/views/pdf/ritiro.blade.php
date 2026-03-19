<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ricevuta di {{ ucfirst($tipo) }} - {{ $numero }}</title>
    <style>
        @page{
        	margin:2mm;
        	size:A4 landscape
        }
        *{
        	box-sizing:border-box
        }
        body{
        	font-family:Arial,sans-serif;
        	font-size:10px;
        	margin:0; 
        	padding:0;
        	color:#000
        }
        .page-container{
        	width:100%
        }
        .ricevuta-wrapper{
        	width:48.5%;
        	float:left;
        	padding:2mm
        }
        /*linea tratteggiata tra le due ricevute */
        .ricevuta-wrapper:first-child{
        	border-right:1px dashed #666;
        }
        .ricevuta{
        	display:table;
        	width:100%;
        	border-collapse:collapse
        }
        .colonna-contenuto{
        	display:table-cell;
        	vertical-align:top;
        	padding:1mm
        }
        .colonna-tipo{
        	display:table-cell;
        	width:10mm;
        	vertical-align:middle;
        	text-align:center;
        	border-left:1px solid #eee
        }
        .header-section{
        	width:100%;
        	border:1px solid #333;
        	margin-bottom:1mm
        }
        .header-table{
        	width:100%;
        	border-collapse:collapse
        }
        .numero-box{
            width:12mm;
            color: white;
            text-align:center;
            vertical-align:middle;
            padding:1mm;
            font-size:14px;
            font-weight:700;
            /*Colori diversi in base al tipo do ricevuta*/
            background-color: {{ ($tipo) === 'vendita' ? 'blue' : (($tipo ) === 'ritiro' ? 'green' : (($tipo ) === 'prenotazione' ? 'purple' : 'gold')) }} 
        }
        .tipo-text{
        	font-size:10px;
        	font-weight:700;
        	margin-top:.5mm;
        	text-transform:uppercase;
        	letter-spacing:.5px
        }
        .info-box{
        	padding:.5mm 1mm;
        	vertical-align:middle;
        	flex:1
        }
        .info-line{
        	margin:.3mm 0;
        	font-size:10px
        }
        .logo-box{
        	width:20mm;
        	text-align:right;
        	vertical-align:middle;
        	padding:.5mm 1mm;
        	font-size:5px;
        	line-height:1.1
        }
        .logo-title{
        	font-weight:700;
        	color:#2563eb;
        	font-size:10px
        }
        table.libri{
        	width:100%;
        	border-collapse:collapse;
        	font-size:10px;
        	margin:1mm 0;
        	flex-grow:1
        }
        table.libri th{
        	background-color:#e0e0e0;
        	color:#000;
        	padding:.5mm;
        	text-align:left;
        	font-weight:700;
        	border:1px solid #999;
        	font-size:10px;
        }
        table.libri td{
        	border:1px solid #ccc;
        	padding:.3mm .5mm;
        	height:3.8mm;
        	font-size:10px;
        }
        table.libri tfoot td{
        	background-color:#f0f0f0;
        	font-weight:700;
        	border:1px solid #999;
        	padding:.5mm
        }
        .avvertenze-box{
        	background:#f5f5f5;
        	border:1px solid #ccc;
        	padding:1mm;
        	font-size:10px;
        	line-height:1.2;
        	margin:1mm 0
        }
        .firma-row{
        	margin-top:1mm;
        	width:100%
        }
        .firma-cell{
        	display:inline-block;
        	width:48%;
        	font-size:10px;
        	vertical-align:bottom
        }
        .firma-cell:first-child{
        	margin-right:2%
        }
        .firma-line{
        	border-bottom:1px solid #333;
        	height:4mm;
        	margin-top:.5mm
        }
    </style>
</head>
<body>
    @php
        $totalPrezzo = 0;
        $totalLibri = count($libri);
    foreach($libri as $libro) {
        $totalPrezzo += $libro->prezzo;
    }

    $limitLast = 31; // Limite righe per l'ultima pagina (con footer)
    $limitFull = 37; // Limite righe per le pagine piene (senza footer)

    // Convertiamo in array per manipolazione
    $processedLibri = $libri instanceof \Illuminate\Support\Collection ? $libri->values()->all() : $libri;
    $chunks = [];

    while (count($processedLibri) > 0) {
        $remaining = count($processedLibri);
        if ($remaining <= $limitLast) {
            // Se sta tutto nell'ultima pagina
            $chunks[] = array_splice($processedLibri, 0, $remaining);
        } elseif ($remaining <= $limitFull) {
            // Caso limite: sta in una pagina piena (30 < x <= 39) ma se lo mettiamo tutto
            // diventa l'ultima pagina e richiede il footer, che non ci sta.
            // Quindi riempiamo la pagina lasciando almeno 1 elemento per la pagina successiva
            $chunks[] = array_splice($processedLibri, 0, $remaining - 1);
        } else {
            // Abbiamo tanti elementi, riempiamo una pagina intera
            $chunks[] = array_splice($processedLibri, 0, $limitFull);
        }
    }
    @endphp
    
    @foreach($chunks as $chunk)
    @php
        $pageLimit = $loop->last ? $limitLast : $limitFull;
    @endphp
    <div class="page-container" style="{{ !$loop->last ? 'page-break-after:always;' : '' }}">
    <!-- Due copie della ricevuta affiancate -->
        @for($copy = 1; $copy <= 2; $copy++) 
        <div class="ricevuta-wrapper">
            <div class="ricevuta">
                <!-- Colonna contenuto centrale -->
                <div class="colonna-contenuto">
                    <!-- Header -->
                    <div class="header-section">
                        <table class="header-table">
                            <tr>
                                <td class="numero-box">
                                    {{ $numero }}
                                    <div class="tipo-text">{{ $tipo }}</div>
                                </td>
                                <td class="info-box">
                                    <div class="info-line"><strong>{{ $nome_utente ?? '' }}</strong></div>
                                    <div class="info-line">{{ $data ?? '' }}</div>
                                </td>
                                <td class="logo-box">
                                    <div class="logo-title">LIBRI USATI</div>
                                    <div>CREMA</div>
                                    <div>EDIZIONE 6</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Tabella libri -->
                    <table class="libri">
                        <thead>
                            <tr>
                                <th style="width:10%;">Codice</th>
                                <th style="width:20%;">ISBN</th>
                                <th style="width:50%;">Titolo</th>
                                <th style="width:10%;">Originale</th>
                                <th style="width:10%;">@if ($tipo === 'ritiro' || $tipo === 'restituzione') Quota @else Prezzo @endif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chunk as $libro)
                                <tr>
                                    <td>{{ $libro->numero_libro }}</td>
                                    <td>{{ $libro->isbn ?? '' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($libro->titolo ?? '', 40) }}</td>
                                    <td>{{ number_format($libro->prezzo_originale ?? 0, 2, '.', '') }}</td>
                                    <td>{{ number_format($libro->prezzo ?? 0, 2, '.', '') }}</td>
                                </tr>
                            @endforeach
                            {{-- Riempiamo le righe vuote fino al limite della pagina --}}
                            @for($i = count($chunk); $i < $pageLimit; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>{{ $loop->last ? 'Totale' : 'Continua...' }}</strong></td>
                                <td>&nbsp;</td>
                                <td><strong>{{ $loop->last ? $totalLibri . ' libri' : '' }}</strong></td>
                                <td>&nbsp;</td>
                                <td><strong>{{ $loop->last ? number_format($totale ?? $totalPrezzo, 2, '.', '') . '€' : '' }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <!-- Avvertenze -->
                    @if($loop->last)
                    <div class="avvertenze-box">
                        Sono a conoscenza di aver acquistato libri usati al prezzo del 50% (40% in caso di libri fuori catalogo) di quello indicato in copertina e sono consapevole the potrebbero essere scritti e/o danneggiati. <br>
                        I LIBRI ACQUISTATI NON SONO RESTITUIBILI. SI ricorda the la vendita avviene esclusimanete nei giorni e nel luoghi Indicati sul sito www.libriusaticrema.it <br>
                        Per informazionI agglornate contattare per mail info@libriusaticrema.it.
                    </div>
                    
                    <!-- Firme -->
                    <div class="firma-row">
                        <div class="firma-cell">
                            <span>Firma:</span>
                            <br><br>
                            <div class="firma-line"></div>
                        </div>
                        <div class="firma-cell">
                            <span>Firma staff:</span>
                            <br><br>
                            <div class="firma-line"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endfor
        <div style="clear:both;"></div>
    </div>
    @endforeach
</body>
</html>
