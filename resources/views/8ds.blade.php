<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>8D's</title>
</head>
<style>
    th,
    td {
        text-align: center;
        font-family: Verdana, Geneva, Tahoma, sans-serif;
    }

    .left {
        text-align: left;
        font-size: 16px;
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
    <table>
        <tr>

            <th><img src="images/epno.png" alt="EP&O" style="width: 100;"></th>
            <td style="font-size: 14px;" colspan="2">RS: EPNO S DE RL DE CV Plaza Solid, Blvrd Municipio Libre 3529 Juárez, Chihuahua, México</td>
            <th><img src="images/esquina.PNG" alt="EP&O" style="width: 100;"></th>

        </tr>

        <tr>

            <th colspan="4">
                <b>NUMERO DE QUEJA <br>
                    {{$complaint_num}}/ {{$date}}
                </b>
            </th>

        </tr>
        <tr>
            <td class="left" colspan="3">Contacto:&nbsp; {{$user_name}} </td>
        </tr>
        <tr>
            <td class="left" colspan="3">Phone:&nbsp; {{$user_phone}} </td>
        </tr>
        <tr>
            <td class="left" colspan="3">E-mail:&nbsp; {{$user_mail}} </td>

        </tr>
        <tr>
    </table>
    <div class="container text-center">
        <div class="row align-items-start">
            <div class="col-12">
                <h3>1D. Información del equipo</h3> <br />
                <label>{{$primer_d}} </label>

            </div>
            <div class="col-12">
                <h3>2D. Descripción del problema</h3> <br />
                <label>{{$segunda_d}} </label>

            </div>
            <div class="col-12">
                <h3>3D. Acciones de contención privicionales (En las primeras 24 hrs)</h3> <br />
                <label>{{$tercer_d}} </label>

            </div>

            <div class="col-12">
                <h3>4D. Define y verifique la(s) causa(s) raíz principal(es)</h3> <br />
                <label>{{$cuarta_d}} </label>

            </div>
            <div class="col-12">
                <h3>5D. Acciones correctivas permanentes</h3> <br />
                <label>{{$quinta_d}} </label>

            </div>
            <div class="col-12">
                <h3>6D. Implementación y validacion de acciones correctivas permanentes</h3> <br />
                <label>{{$sexta_d}} </label>

            </div>
            <div class="col-12">
                <h3>7D. Acciones para prevenir que recurra el incidente</h3> <br />
                <label>{{$septima_d}} </label>

            </div>
            <div class="col-12">
                <h3>8D. Equipo y reconocimiento individual</h3> <br />
                <label>{{$octava_d}} </label>

            </div>
        </div>

    </div>

</body>

</html>