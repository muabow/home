"""
https://justkode.kr/python/flask-restapi-1
파이선, Rest API  서버, 보안, Slaggle.io Framework
을 보면, 1-2-3 의 포스팅이 있고, 이를 참고함 

설치 package.
$ python 3.97을 사용하고 있음 
$ pip install flask
$ pip install flask-restx
"""
import json
import copy
import datetime
from flask import Flask, request # 서버 구현을 위한 Flask 객체 import
from flask_restx import Api, Resource  # Api 구현을 위한 Api 객체 import
from collections import OrderedDict

app = Flask(__name__)  # Flask 객체 선언, 파라미터로 어플리케이션 패키지의 이름을 넣어줌.
api = Api(
    app,
    version='1.0.0.0',
    title="ISLS API  TEST Server",
    description="ISLS Todo API Server!",
    terms_url="/",
    contact="imk",
    license="MIT"
)  # Flask 객체에 Api 객체 등록

def make_response_message(_code, _data = ""):
    arr_http_status = OrderedDict()
    arr_http_status[100] = "Continue"
    arr_http_status[101] = "Switching Protocols"
    arr_http_status[200] = "OK"
    arr_http_status[201] = "Created"
    arr_http_status[202] = "Accepted"
    arr_http_status[203] = "Non-Authoritative Information"
    arr_http_status[204] = "No Content"
    arr_http_status[205] = "Reset Content"
    arr_http_status[206] = "Partial Content"
    arr_http_status[300] = "Multiple Choices"
    arr_http_status[301] = "Moved Permanently"
    arr_http_status[302] = "Found"
    arr_http_status[303] = "See Other"
    arr_http_status[304] = "Not Modified"
    arr_http_status[305] = "Use Proxy"
    arr_http_status[306] = "(Unused)"
    arr_http_status[307] = "Temporary Redirect"
    arr_http_status[400] = "Bad Request"
    arr_http_status[401] = "Unauthorized"
    arr_http_status[402] = "Payment Required"
    arr_http_status[403] = "Forbidden"
    arr_http_status[404] = "Not Found"
    arr_http_status[405] = "Method Not Allowed"
    arr_http_status[406] = "Not Acceptable"
    arr_http_status[407] = "Proxy Authentication Required"
    arr_http_status[408] = "Request Timeout"
    arr_http_status[409] = "Conflict"
    arr_http_status[410] = "Gone"
    arr_http_status[411] = "Length Required"
    arr_http_status[412] = "Precondition Failed"
    arr_http_status[413] = "Request Entity Too Large"
    arr_http_status[414] = "Request-URI Too Long"
    arr_http_status[415] = "Unsupported Media Type"
    arr_http_status[416] = "Requested Range Not Satisfiable"
    arr_http_status[417] = "Expectation Failed"
    arr_http_status[500] = "Internal Server Error"
    arr_http_status[501] = "Not Implemented"
    arr_http_status[502] = "Bad Gateway"
    arr_http_status[503] = "Service Unavailable"
    arr_http_status[504] = "Gateway Timeout"
    arr_http_status[505] = "'HTTP Version Not Supported"

    if _code in arr_http_status:
        status = arr_http_status[_code]
    else:
        status = arr_http_status[500]

    json_message = OrderedDict()
    json_message["code"]    = _code
    json_message["message"] = status
    json_message["result"]  = _data

    return json_message, _code

def get_json_data():
    # TODO, 파일 경로에 대한 검증
    with open("isls_api.json", "r", encoding="utf8") as fp: 
        contents  = fp.read()
        json_data = json.loads(contents)
        return json_data

def set_json_data(_json_data):
    with open("isls_api.json", "w", encoding="utf8") as fp:
        fp.write(json.dumps(_json_data, ensure_ascii=False, indent=4))
    return

def check_category_system(_uri, _key, _value):
    if( "isls-api/System/Root/Date" == _uri ):
        if( _key == "Time"):
            arr_date_format = _value.split(' ')
            if( len(arr_date_format) != 2 ):
                return False
            
            regex = datetime.datetime.strptime
            try:
                regex(arr_date_format[0], '%Y-%m-%d')
                regex(arr_date_format[1], '%H:%M:%S')

            except ValueError:
                return False
        
        elif( _key == "TimeServer" ):
            if( _value["Enable"] == "on" or _value["Enable"] == "off" ):
                return True
            else: 
                return False
    
    return True

def check_valid_value(_uri, _key, _value):
    print("uri    : {}".format(_uri))
    print("key    : {}".format(_key))
    print("_value : {}".format(_value))

    category = _uri.split("/")[1]
    if( category == "System" ): return check_category_system(_uri, _key, _value)
        

    return True

@api.route('/<path:uri>')
class ISLSAPI(Resource):
    def get(self, uri): 
        json_data = get_json_data()

        # URI 요청 경로 배열화
        arr_uri_list = uri.split('/')
        arr_uri_list = ' '.join(arr_uri_list).split()

        # URI - JSON matching        
        str_not_exist_uri   = ""

        for uri_info in arr_uri_list:
            if uri_info in json_data:
                json_data = json_data[uri_info]

            else:
                str_not_exist_uri = uri_info
                break

        if( str_not_exist_uri != "" ):
            return make_response_message(400, "Invalid URI path : [{}]".format(str_not_exist_uri))
        
        return make_response_message(200, json_data)

    def post(self, uri): 
        json_save = json_data = get_json_data()

        # URI 요청 경로 배열화
        arr_uri_list = uri.split('/')
        arr_uri_list = ' '.join(arr_uri_list).split()

        # URI - JSON matching        
        str_not_exist_uri = ""
        obj_attribute_info  = None

        for uri_info in arr_uri_list:
            if uri_info in json_data:
                json_data = json_data[uri_info]

                if "Attributes" in json_data:
                    obj_attribute_info = json_data["Attributes"]

            else:
                str_not_exist_uri = uri_info
                break
        
        if( str_not_exist_uri != "" ):
            return make_response_message(400, "Invalid URI path : [{}]".format(str_not_exist_uri))
        
        if( obj_attribute_info == None ): 
            return make_response_message(400, "Not found Attributes, Invalid path: [{}]".format(uri))

        if "SupportAPI" not in obj_attribute_info or obj_attribute_info["SupportAPI"] != "enable":
            return make_response_message(400, "Not support API, Invalid path : [{}]".format(uri))
        
        if "SupportMethod" not in obj_attribute_info or "POST" not in obj_attribute_info["SupportMethod"]: 
            return make_response_message(400, "Not support POST method : [{}]".format(uri))

        # POST 데이터 처리
        request_data = request.get_json()
        if "Attributes" in request_data:
            del request_data["Attributes"]

        # Key 검사
        # TODO, URI 마지막 / 중복 입력 시 pass 되는 현상 파악
        # TODO, value 내 key/value 존재 시 해당하는 key/value만 업데이트 하도록 변경
        is_not_found   = True
        is_valid_value = True
        arr_invalid_key = []
        for key in request_data:
            if key in json_data:
                json_data[key] = request_data[key]
                if( check_valid_value(uri, key, json_data[key]) == False ):
                    is_valid_value = False
                    break

                is_not_found = False
            else:
                arr_invalid_key.append(key)

        if( is_valid_value == False ):
            json_message = OrderedDict()
            json_message["uri"]            = uri + "/" + key
            json_message["invalid_format"] = json_data[key]
            return make_response_message(400, json_message)

        if( is_not_found == True ):
            return make_response_message(400, "Not found key : {}".format(arr_invalid_key))

        ptr_json = copy.copy(json_save)
        for uri_info in arr_uri_list:
            if uri_info in ptr_json:
                ptr_json = ptr_json[uri_info]

        ptr_json = json_data
        set_json_data(json_save)

        if "Attributes" in json_data:
            del json_data["Attributes"]
            
        return make_response_message(200, json_data)

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=80)