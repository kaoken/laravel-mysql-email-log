@php
    $cssClass = "text-secondary";
    if( $log->level >= 400)
        $cssClass = "text-danger";
    else if( $log->level >= 300)
        $cssClass = "text-warning";
    else if( $log->level >= 200)
        $cssClass = "text-info";
    $o = $log->getJsonDecodeData();
    $context="";
    if( array_key_exists('exception', $o) && array_key_exists('xdebug_message', $o['exception'])){
        $context = $o['exception']['xdebug_message'];
        if( preg_match("/^\n/", $context)){
            $context = nl2br(html_entity_decode($context));
        }else if(preg_match("/^</", $context)){
            ;
        }else{
            $context = nl2br(html_entity_decode($context));
        }
    }else{
        $context = '<table class="table table-striped table-sm">';
        $context .= '<tbody>';
        foreach ($o as $key=>$value) {
            $context.='<tr><th>'.$key.'</th>';
             if( is_array($value) || is_object($value))
                $value = json_encode($value);
           $context.= '<td>'.nl2br(html_entity_decode($value)).'</td></tr>';
        }
        $context .= '</tbody></table>';
    }
@endphp
<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous"></head>
<body>
<div class="container">
    <h1 class="{{$cssClass}}">{{$log->level_name}}</h1>
    <h2 class="text-danger">Log mail send limit"{{$config['max_email_send_count']}}" exceeded.</h2>
    <table class="table table-striped table-sm">
        <tbody>
        <tr>
            <th>Date</th>
            <td>{{$log->create_tm}}</td>
            <th>PID</th>
            <td>{{$log->pid}}</td>
            <th>IP</th>
            <td>{{$log->ip}}<br />{{@gethostbyaddr($log->ip)}}</td>
            <th>HTTP request mthod</th>
            <td>{{$log->method}}</td>
        </tr>
        @if(!empty($log->route))
            <tr>
                <th>Route</th>
                <td colspan="7">{{$log->route}}</td>
            </tr>
        @endif
        @if(!empty($log->user_agent))
            <tr>
                <th>User Agent</th>
                <td colspan="7">{{$log->user_agent}}</td>
            </tr>
        @endif
        <tr>
            <th>Message</th>
            <td colspan="7">{{$log->message}}</td>
        </tr>
        </tbody>
    </table>
    <div class="card">
        <div class="card-header"><h3>Context</h3></div>
        <div class="card-body">
            {!! $context !!}
        </div>
    </div>
</div>
</body>
</html>