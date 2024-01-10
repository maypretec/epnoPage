<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    th,
    td {
        text-align: center;
        font-family: Verdana, Geneva, Tahoma, sans-serif;
    }

    .left {
        text-align: left;
        font-size: 12px;
    }

    .center {
        text-align: center;
        font-size: 12px;
    }

    .color-fondo {
        background-color: #5b9bd5;
        color: white;
        /* border: 1px solid black !important; */
    }

    .td-fondo {
        background-color: #f0f4ef;
    }
</style>

<body>
    <table cellspacing="5">
        <tr>

            <th><img src="images/epno.png" alt="EP&O" style="width: 100;"></th>
            <td style="font-size: 14px;" colspan="2">RS: EPNO S DE RL DE CV Plaza Solid, Blvrd Municipio Libre 3529 Juárez, Chihuahua, México</td>
            <th><img src="images/esquina.PNG" alt="EP&O" style="width: 100;"></th>

        </tr>

        <tr>

            <td COLSPAN="4"> <b>PURCHASE ORDER <br>
                    PO number/date {{$purchase}}/ {{$date}} </b> </td>

        </tr>
        <tr>
            <td class="left" colspan="3">Buyer:&nbsp; {{$user_name}} </td>
        </tr>
        <tr>
            <td class="left" colspan="3">Phone:&nbsp; {{$user_phone}} </td>
        </tr>
        <tr>
            <td class="left" colspan="3">E-mail:&nbsp; {{$user_mail}} </td>

        </tr>
        <tr>
            <td class="left" colspan="3">Seller:&nbsp; {{$seller}} </td>
        </tr>
        @if($tipo_cambio>0)
        <tr>
            <td class="left" colspan="3">Exchange rate:&nbsp;$ {{$tipo_cambio}} </td>
        </tr>
        @endif

        <tr>
            <td class="left" rowspan="7" colspan="2" style="vertical-align: text-top; border: 1px solid black; width: 1000px;"><b>Special Instruccions:</b> <br>
                {{$special_inst}}
            </td>
            <td class="left" colspan="2"> <b>Ordering Address:</b> {{$ordering_address}} </td>
            <!-- <td class="left"> </td> -->
            <!-- <td class="left"> </td> -->

        </tr>
        <tr>
            <td class="left" colspan="2"> <b>Billing Address:</b> {{$billing_address}} </td>
            <td class="left"> </td>
            <td class="left"> </td>
            <td class="left"> </td>

        </tr>
        <tr>
            <td class="left" colspan="2"><b>Terms of Payment: </b> {{$dias_validos}} business days </td>
            <td class="left"> </td>
            <td class="left"> </td>
            <td class="left"> </td>
        </tr>

        <tr>
            <td class="left" colspan="2"> <b>Currency:</b> {{$currency}} </td>
            <td class="left"></td>
            <td class="left"></td>
            <td class="left"></td>
        </tr>
        <tr>
            <td class="left" colspan="2"><b>Delivery Address: </b> {{$delivery_address}} </td>
            <td class="left"></td>
            <td class="left"></td>
            <td class="left"></td>
        </tr>
        <tr>
            <td class="left" colspan="2"><b>Terms of Delivery: </b> {{$delivery_terms}} </td>
            <td class="left"></td>
            <td class="left"></td>
            <td class="left"></td>
        </tr>
        <tr>
            <td class="left" colspan="2"><b>Shipping Instructions: </b> {{$shipping_inst}} </td>
            <td class="left"></td>
            <td class="left"></td>
            <td class="left"></td>
        </tr>
    </table>
    <table style="margin-top: 25; font-size: 12px;">
        <tr>
            <th class="color-fondo">CODE</th>
            <th class="color-fondo">MATERIAL-DESCRIPCION</th>
            <th class="color-fondo">QTY</th>
            <th class="color-fondo">U/M</th>
            <th class="color-fondo">UNIT PRICE ({{$currency}})</th>
            <th class="color-fondo">NET VALUE ({{$currency}})</th>
            <th class="color-fondo">DATE</th>
        </tr>
        @foreach ($products as $p)
        <tr>
            <td class="td-fondo">{{str_pad($loop->iteration, 4, '0', STR_PAD_LEFT)}} </td>
            <td class="td-fondo" style="width: 250px;">{{$p['descripcion']}} </td>
            <td class="td-fondo">{{$p['qty']}} </td>
            <td class="td-fondo">{{$p['um']}} </td>
            <td class="td-fondo">$ {{$p['precio_unitario']}} </td>
            <td class="td-fondo">$ {{$p['net_value']}} </td>
            <td class="td-fondo">{{$date}} </td>
        </tr>

        @endforeach
        <tr>
            <td class="td-fondo">&nbsp;</td>
            <td class="td-fondo">&nbsp;</td>
            <td class="td-fondo">&nbsp;</td>
            <td class="td-fondo">&nbsp;</td>
            <td class="td-fondo">&nbsp;</td>
            <td class="td-fondo">&nbsp;</td>
            <td class="td-fondo">&nbsp;</td>
        </tr>
        <tr>
            <td class="td-fondo"></td>
            <td class="td-fondo"></td>
            <td class="td-fondo"></td>
            <td class="td-fondo"></td>
            <td class="td-fondo"></td>
            <td class="color-fondo">TOTAL NET</td>
            <td class="td-fondo"> $ {{$final_cost}} </td>
        </tr>
    </table>
</body>

</html>