<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Services quote</title>
</head>
<style>
    /* DivTable.com */
    .divTable {
        display: table;
        width: 100%;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10;
    }

    .divTableRow {
        display: table-row;
    }

    .divTableHeading {
        background-color: #EEE;
        display: table-header-group;
    }

    .divTableCell,
    .divTableHead {
        /* border: 1px solid #999999; */
        display: table-cell;
        padding: 3px 10px;
        text-align: center;
        line-height: 1.5;
        vertical-align: middle;
    }

    .divTableCellB,
    .divTableHead {
        border-bottom: 1px solid black;
        display: table-cell;
        padding: 3px 10px;
        text-align: center;
        vertical-align: middle;
    }

    .divTableHeading {
        background-color: #EEE;
        display: table-header-group;
        font-weight: bold;
    }

    .divTableFoot {
        background-color: #EEE;
        display: table-footer-group;
        font-weight: bold;
    }

    .divTableBody {
        display: table-row-group;
    }
</style>

<body style="font-family: Arial, Helvetica, sans-serif; font-size: 10;border: 1px solid black;">
    <div style="margin:5px;">

        <div class="divTable">
            <div class="divTableBody">
                <div class="divTableRow">
                    <div class="divTableCell"><img src={{'storage/uploads/'. $logo}}
                     alt="Empresa" style="width: 120;"></div>
                    <div class="divTableCell">&nbsp;RS: {{$org_supp}}, {{$calle}} {{$num_ext}} {{$ciudad}}, {{$estado}}, {{$pais}}</div>
                    <div class="divTableCell">Quote: {{$purchase}} Date: {{$date}}</div>
                </div>
            </div>
        </div>
        <div style="line-height: 1.5;">
            <b>Client:</b> {{$org}} <br>
            <b>Contact:</b> {{$client_name}} <br>
            @if($tipo_cambio>0)
            <b>Exchange rate:</b> ${{$tipo_cambio}} 
            @endif

        </div>
        <div class="divTable" style="margin-top: 25;">
            <div class="divTableBody">
                <div class="divTableRow">
                    <div class="divTableCellB">CODE</div>
                    <div class="divTableCellB">UM</div>
                    <div class="divTableCellB">QTY</div>
                    <div class="divTableCellB">DESCRIPTION</div>
                    <div class="divTableCellB">UNIT PRICE ({{$currency}})</div>
                    <div class="divTableCellB">TOTAL PRICE ({{$currency}})</div>
                </div>
                
                @foreach ($products as $p)

                <div class="divTableRow">
                    <div class="divTableCell">{{str_pad($loop->iteration, 4, '0', STR_PAD_LEFT)}} </div>
                    <div class="divTableCell">{{$p['um']}} </div>
                    <div class="divTableCell">{{$p['qty']}} </div>
                    <div class="divTableCell">{{$p['descripcion']}} </div>
                    <div class="divTableCell">$ {{$p['precio_unitario']}}</div>
                    <div class="divTableCell">$ {{$p['qty']*$p['precio_unitario']}}</div>
                </div>

                @endforeach

                <div class="divTableRow">
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                </div>
                <div class="divTableRow">
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                </div>
                <div class="divTableRow">
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">Net</div>
                    <div class="divTableCell">$ {{$subtotal}}</div>
                </div>
                <div class="divTableRow">
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">IVA
                        @if($iva==0)
                        (No aplica)
                        @else
                        ({{$iva}}%)
                        @endif

                    </div>
                    <div class="divTableCell">${{$precio_iva}}</div>
                </div>
                <div class="divTableRow">
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">&nbsp;</div>
                    <div class="divTableCell">Total</div>
                    <div class="divTableCell">${{$total}}</div>
                </div>
            </div>
        </div>
        <!-- DivTable.com -->

        <div style="width: 100%; line-height: 1.8; font-family: Arial, Helvetica, sans-serif; font-size: 10;margin-top: 15;">
            <b>This quote is subject to the following terms and conditions that are set forth below:</b> <br>
            <label for="">1. The indicated price is subject to the conditions indicated by the client, in case of making changes, the price may be affected.</label> <br>
            <label for=""> 2. Delivery: </label><b>{{$fecha_entrega}}</b> <br>
            <label>3. This quote is valid for </label> <b>{{$vigencia}} days</b> <br>
            <label>4. Payment Terms: </label> <b>{{$dias_validos}} days </b> <br>
            <!-- <label for="">5. This quote does not include customs and logistics expenses originated by the process, we will use a third party
                vendor to provide this service</label> -->
            <!-- Variable el nombre del agente -->
            <label>Quote made by: </label> <b style="text-decoration: underline;text-transform: uppercase;"> {{$user_name}} </b> <br>

        </div>
        <div style="text-align: center;line-height: 1.8;margin-top: 25px;">
            <label for="">"Simple solutions to big problems"</label> <br>
            <a href="https://epno.com.mx/">https://epno.com.mx/</a> <br>
            <!-- <label for="">Visit our Social Networks:</label> <br> -->
        </div>
        <!-- <div style="margin-top: 15px; margin-right: 235px;margin-left: 235px;">

            <a href="https://www.facebook.com/epnomx">
                <img src="images/fb.png" alt="FB" style="width: 35;">
            </a>
            <a href="https://www.linkedin.com/company/epno/mycompany/">
                <img src="images/linkedIn_PNG38.png" alt="LinkedIn" style="width: 25;">
            </a>
            <a href="mailto: jorge.carreon@epno.com.mx">
                <img src="images/gmail.png" alt="FB" style="width: 38;">
            </a>
            <a href="https://www.youtube.com/watch?v=BsBicgcWwUg">
                <img src="images/yb.png" alt="Youtube" style="width: 30;">
            </a>

        </div> -->
        <div style="margin-top: 15; font-style: italic; text-align: center;">
            <label for="">The prices, descriptions and details of the products are subject to change and without prior notice. </label>
        </div>
    </div>

</body>

</html>