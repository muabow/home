<?php
/* 최초 작성일 : 2017.01.10 / 마지막 수정일 : 2017.01.10 / ver. 1.0.0.0
 *
 * Module 개발 가이드 문서입니다.
 * 해당 문서를 참고 하시어 Module을 개발 하시면 됩니다.
 * Module 개발 후 Guide 주석은 모두 삭제해 주세요.

 * 문서 신규 작성 또는 복사 시 소유주(Owner) 및 퍼미션(Permission, 755)를 확인 하세요.
*/
	// 기본 include format 은 지켜주세요.
	$env_pathModule = str_replace(basename(__FILE__), "", realpath(__FILE__));

	include_once $env_pathModule . "common/common_define.php";
	include_once $env_pathModule . "common/common_script.php";
?>

<link rel="stylesheet"	href="<?=Guide\Def\PATH_WEB_CSS_STYLE ?>" type="text/css" />

<div id="div_contents_guide" style="padding-left: 10px;">
	<br />

	<h1> 모듈 가이드 입니다.</h1>
	<br />

	<p> 최초 작성일 : 2017.01.10 / 마지막 수정일 : 2017.02.01 / ver. 1.3.0.0</p>
	<p>&nbsp; - ver. 1.3.0.0 : Module 추가 - 장치 모니터</p>
	<p>&nbsp; - ver. 1.2.0.0 : 로그인 화면 및 기능 추가, RSA/SHA256 암호화 적용</p>
	<p>&nbsp; - ver. 1.1.0.1 : LOG Class 기능 개선(PHP), LOG Class 사용 추가(Javascript)</p>
	<p>&nbsp; - ver. 1.1.0.0 : LOG Class 사용 요령(PHP) 추가, 공통 - 퍼미션 변경 방법 추가</p>
	<p>&nbsp; - ver. 1.0.0.0 : 초안 작성</p>
	<br />

	<h3> 문서의 목적과 정보 </h3>
	<p> - 해당 문서는 Module Template의 활용 방법을 목적으로 합니다. </p>
	<p> - Module Template는 HTML5, CSS3, PHP, javascript로 구성되어 있으며 각 언어별 객체(Object)에 대한 이해가 필요합니다.  </p>
	<p> - 세부 내용은 해당 파일의 주석 또는 배포된 Web Module 구조화 관련 문서를 참고 하세요. </p>
	<p> - 본 문서는 업데이트 중 입니다. </p>
	<p> - Module Template는 업데이트 중 입니다. (TODO) </p>
	<p> &nbsp;&nbsp;1) Header : 로그인 처리, 사용자 정보, 관리자 설정, 로그 아웃 </p>
	<p> &nbsp;&nbsp;2) Header : 장비 반영 후 네트워크, 호스트 정보 등 호출 </p>
	<p> &nbsp;&nbsp;3) 기타 요청 사항 </p>
	<br />
	<br />

	<h3> 공통 </h3>
	<p> * Module Template의 소스코드를 임의로 수정하지 마세요. 추가/변경이 필요할 경우 담당자를 통해서 반영하세요. </p>
	<p> - Module Template는 모두 상대 경로를 참조하기 때문에 본인의 테스트 서버로 복사하여 사용하셔도 됩니다. </p>
	<p> - Module 디렉토리 이하 bin, conf, html, log 의 기본 구조는 변경하면 안되고, 임의의 추가는 가능 합니다.</p>
	<p> - html 디렉토리 이하 common, css, img, js, language_packs 의 기본 구조는 변경하면 안되고, 임의의 추가는 가능 합니다. </p>
	<p> - common 파일은 개발자 임의로 추가 및 삭제가 가능 합니다. </p>
	<p> - Module 구성 전 Module Template의 소스 코드를 파악하여 문법과 규칙에 대해서 충분히 숙지하길 바랍니다. </p>
	<p> * Module 및 파일의 복사 또는 추가 시 퍼미션이 맞지 않아 페이지에 접근할 수 없는 문제가 발생할 수 있습니다. </p>
	<p> &nbsp;&nbsp; <a href="http://<?=$_SERVER['SERVER_NAME'] ?>/script/exec_permModule.php" target="_new">"http://<?=$_SERVER['SERVER_NAME'] ?>/script/exec_permModule.php"</a> 를 실행하여 Module 이하 모든 디렉토리와 파일들의 퍼미션을 755로 변경합니다. </p>
	<br />
	<br />

	<h3> HTML 작성 요령 </h3>
	<p> - HTML 파일 경로 : <?php echo $env_pathModule . "common/guide.php" ?></p>
	<p> - Module의 시작이 되는 문서로 Module명과 파일명은 동일해야 합니다.</p>
	<p> - DOCTYPE과 HTML 선언 TAG는 Module Template에서 사용되었기 때문에 Module 내에서는 사용하지 않습니다.</p>
	<p> - Module의 HTML 본문은 div로 시작 합니다. </p>
	<p> - div의 id는 명명규칙(Naming Rule)에 따라 작성 합니다. (ex. div_contents_audio) </p>
	<p> - div의 style 또는 class는 편의대로 적용 합니다. </p>
	<br />
	<br />

	<h3> CSS 작성 요령 </h3>
	<p> - CSS 파일 경로 : <?php echo $env_pathModule . "common/css.php" ?></p>
	<p> - 문서 전체에 영향을 끼치는 *, html, body, a 등 태그의 직접적인 사용은 지양합니다. </p>
	<p> - ID 또는 CLASS를 지정하여 사용합니다. </p>
	<p> &nbsp;&nbsp;ex) #div_frame { ... }, .span_header { ... } </p>
	<p> - <span id="span_test"> span id  스타일 테스트 </span> </p>
	<p>* 3. 문서 전체에 영향을 주는 스타일이 있습니다. Template의 css/style.css를 참고하세요. </p>
	<br />
	<br />

	<h3> PHP 작성 요령 </h3>
	<p> - PHP 파일 경로 : <?php echo $env_pathModule . "common/common_script.php" ?> </p>
	<p> - 모든 변수와 함수는 namespace 내에 위치 합니다. (ex. namespace Guide\Func) </p>
	<p> - 해당 Module의 기능을 수행하는 변수와 함수는 Class로 구성하여 사용합니다. (ex. class GuideFunc { ... } </p>
	<p> &nbsp;&nbsp;* 이는 Module Template와 Module 간 충돌, 그리고 Module과 Module관 충돌을 막기 위함 입니다. </p>
	<p> &nbsp;&nbsp;* 문법 및 자세한 내용은 Module Template의 common/common_script.php, common_js.php 파일을 참고 합니다. </p>
	<p> - Ajax 또는 form submit 등 POST 시 처리하는 별도의 파일을 만드는 것을 권장 합니다. </p>
	<br />
	<br />

	<h3> javascript 작성 요령 </h3>
	<p> - javascript 파일 경로 : <?php echo $env_pathModule . "common/common_js.php" ?> </p>
	<p> - id, name, class 등을 셀렉터(selector)로 사용 시 $(document).ready(function() { ... } 내부에 작성 합니다. </p>
	<p> &nbsp;&nbsp;예) $("#input_guide_cnt_test").click(function() { }); </p>
	<p> - 그 외에 모든 변수, 함수, 상수는 namespace를 작성하여 사용 합니다. </p>
	<p> &nbsp;&nbsp;예) var Guide = { cnt : 0, ... };  </p>
	<p> - 자세한 내용은 위에 javascript 파일 내부 주석 참조 </p>
	<p> - javascript 샘플 함수 구현 (소스코드 참조) </p>
	<p> &nbsp;&nbsp;<input type="button" id="input_guide_cnt_test" value="클릭 시 숫자가 증가 합니다." /> </p>
	<br />
	<br />

	<h3> 언어팩 작성 요령 </h3>
	<p> - 언어팩 경로 : <?php echo $env_pathModule . "language_pack/" ?> </p>
	<p> - 언어팩도 namespace를 사용하고 namespace에는 모듈명 입력하는데 첫글자는 대문자로 합니다. (ex. Guide\Lang)</p>
	<p> - language_packs 디렉토리 내부에 있는 모든 언어팩들의 내용은 모두 동일해야 합니다. </p>
	<p> - 언어팩 추가 시 환경구성 파일(env.json)에 명시를 합니다. </p>
	<p> * 세부 내용은 배포된 Web Module 구조화 관련 문서를 참고 하세요. </p>
	<br />
	<br />

	<h3> 환경구성 파일(JSON) 작성 요령 </h3>
	<p> - 환경구성 파일 : (Module Template 위치) env.json </p>
	<p> * 세부 내용은 배포된 Web Module 구조화 관련 문서를 참고 하세요. </p>
	<br />
	<br />

	<h3> LOG Class 사용 요령 (PHP) </h3>
	<p> - Module 문서 내에 CommonLogFunc("module명") 로그 클래스를 추가 합니다. </p>
	<p> - module명을 추가하지 않으면 "Class [CommonLogFunc]를 사용할 수 없습니다. Module명을 입력하세요."라는 에러를 출력합니다. </p>
	<p> - 에러 레벨은 총 5단계로 다음과 숫자가 높을수록 고위험을 나타냅니다.</p>
	<p> (ex. $logger = new Common\Func\CommonLogFunc("guide"); </p>
	<p> &nbsp;&nbsp;1. FATAL&nbsp;: $logger->fatal("FATAL 단계 입니다."); </p>
	<p> &nbsp;&nbsp;2. ERROR&nbsp;: $logger->error("ERROR 단계 입니다."); </p>
	<p> &nbsp;&nbsp;3. WARNING&nbsp;: $logger->warn("WARNING 단계 입니다."); </p>
	<p> &nbsp;&nbsp;4. INFO&nbsp;: $logger->info("INFO 단계 입니다.");  </p>
	<p> &nbsp;&nbsp;5. DEBUG&nbsp;: $logger->debug("DEBUG 단계 입니다.");  </p>
	<p> - 로그 메시지는 [년/월/일 시/분/초] [로그레벨] 메시지 로 구성되어 있습니다.</p>
	<p> &nbsp; ex) [2017/01/11 16:14:31] 로그 함수 테스트 입니다.~ </p>
	<p> &nbsp; ex) [2017/01/11 16:14:31][FATAL] level - fatal, 로그 함수 테스트 입니다.~ </p>
	<p> * [INFO] 레벨은 기본적으로 레벨을 출력하지 않고 출력 상태 변환을 통해서 레벨을 출력할 수 있습니다.</p>
	<p> - $logger->setLogInfo(true); // true = 레벨 출력, false = 출력 금지(기본값) </p>
	<p> - 해당 로그는 Module의 log 디렉토리에 Module명.log로 생성 됩니다. (ex. guide.log) </p>
	<p> - 만일 로그 클래스 생성 시 잘못된 Module명을 입력했다면 Template의 log 디렉토리에 저장됩니다.  </p>
	<p> * 로그 디렉토리의 퍼미션은 777로 부여해야 합니다. </p>
	<p> - TODO : LOG VIEWER 개발 중 (17.01.14 시작) </p>
	<br />
	<br />
	<h3> LOG Class 사용 요령 (javascript) </h3>
	<p> - PHP LOG Class와 사용 요령이 동일합니다.</p>
	<p> (ex. var logger = new CommonLogFunc("guide"); </p>
	<br />
	<br />
	<h3> 로그인 프로세스 </h3>
	<p> - 로그인 계정은 [dev], [admin], [operator], [user] 총 4단계로 구분하여 사용 합니다.</p>
	<p> - [dev]는 개발자 권한으로 숨겨진(Hidden) 관리 항목들까지 사용가능 합니다.</p>
	<p> - [admin]는 관리자 권한으로 장치에 대한 모든 기능을 사용할 수 있습니다.</p>
	<p> - [operator]는 운영자 권한으로 대리점, CS/SI 등 현장/기술 구성원들이 사용하는 계정입니다. 시스템에 영향을 주는 기능은 사용할 수 없습니다.</p>
	<p> - [user]는 고객들이 사용하는 사용자 권한으로, 설정과 관련된 기능들은 사용할 수 없습니다.</p>
	<p> - * TODO : 사용자별 권한 제어 기능 개발 예정</p>
	<p> - 계정과 패스워드는 RSA와 SHA256을 이용하여 비밀번호가 아닌 키페어 확인과 hash값 대조를 통해 사용자 인증을 거칩니다.</p>
	<br />
	<br />
	<h3> Module - 장치 모니터 </h3>
	<p> - 장치의 상태(전원, 오디오 on/off)와 오디오 Level meter를 실시간으로 감시하는 모듈 </p>
	<p> - AOE-212N 한정으로 AOE Client 기능이 있는 장치만 사용 가능 (PMU-N 등) </p>
	<p> - 상세 내용 추후 추가 </p>
	<br />
	<br />

	<h3> 기타 </h3>
	<p> - bin, conf 등 관련 내용 작성 예정 </p>
	<br />
	<br />
</div>

<!-- javascript 는 해당 파일 안에 작성 하시면 됩니다. -->
<?php include $env_pathModule . "common/common_js.php"; ?>