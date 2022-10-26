<?php

sendInfo();

// функция определения Ip юзера
function getIp()
{
    foreach (array('HTTP_LSWCDN_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR',
                 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',
                 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                if (filter_var(
                        $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE |
                        FILTER_FLAG_NO_RES_RANGE
                    ) !== false
                ) {
                    return $ip;
                }
            }
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
}

function sendInfo()
{
    try {
        $subid = empty($_REQUEST['sub_id1']) ? '' : $_REQUEST['sub_id1'];
        $firstName = htmlspecialchars($_POST['firstname'] ?? '');
        $lastName = htmlspecialchars($_POST['lastname'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $telephone = htmlspecialchars($_POST['tel'] ?? '');
        $removed = array(" ", "(", ")", "-", "u");
        $onlyValidPhone = str_replace($removed, "", $telephone);
        $ip = getIp();
        $endpoint = "http://165.22.66.202/19ae611/postback?status=lead";
        $data = $ip . "|" . $firstName . "|" . $lastName . "|" . $onlyValidPhone . "|" . $email;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt(
            $ch, CURLOPT_POSTFIELDS, http_build_query(
                [
                    "sub_id_13" => $data,
                    "subid" => $subid]
            )
        );

        $output = curl_exec($ch);
        if (empty($output)) {
            curl_close($ch);
        }

        $response = json_decode($output);
        if (empty($response)) {
            curl_close($ch);
        }

        if (!in_array(curl_getinfo($ch, CURLINFO_HTTP_CODE), [200, 201])) {
            curl_close($ch);
        }
        console_log($response);
        curl_close($ch);
    } catch (ErrorException $exc) {
        http_response_code(400);
        echo $exc->getMessage();
    }
}