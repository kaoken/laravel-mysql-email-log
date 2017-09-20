<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        @php
            $cssClass = "text-secondary";
            if( $log->level >= 400)
                $cssClass = "text-danger";
            else if( $log->level >= 300)
                $cssClass = "text-warning";
            else if( $log->level >= 200)
                $cssClass = "text-info";
        @endphp
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
                <th>Method</th>
                <td>{{$log->method}}</td>
            </tr>
            @if(!empty($log->route))
                <tr>
                    <th>Route</th>
                    <td colspan="6">{{$log->route}}</td>
                </tr>
            @else
                @if(!empty($log->user_agent))
                    <tr>
                        <th>User Agent</th>
                        <td colspan="6">{{$log->user_agent}}</td>
                    </tr>
                @else
                    <tr>
                        <th>Message</th>
                        <td colspan="6">{{$log->message}}</td>
                    </tr>
            </tbody>
        </table>
        <div class="card" style="width: 20rem;">
            <div class="card-header">
                Context
            </div>
            <div class="card-body">
                @php
                    $o = $log->getJsonDecodeData();
                    $context="";
                    if( isset($o['exception']) && isset($o['exception']['xdebug_message'])){
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
                        $context += '<tbody>';
                        foreach ($o as $key=>$value) {
                            $context+='<tr><th>'.$key.'</th>';
                            $context+= '<td>'.nl2br(html_entity_decode($value)).'</td></tr>';
                        }
                        $context += '</tbody>';
                    }
                @endphp
                {!! $context !!}
            </div>
        </div>
    </div>
</div>
</body>
</html>