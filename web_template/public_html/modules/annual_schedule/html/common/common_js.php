<?php 
/************************************
 * Javascript 구간 
 * 언어팩, 상수, 경로 등 메크로를 사용하기 위해서 PHP 내에 script로 작성합니다.
 ************************************/
    // 해당 출력은 삭제하세요.
    
?>
<script type="text/javascript">
    // Common Functions
    /* document elements loading 완료 후 처리 */
    $(document).ready(function() {
        
        // namespace 접근/호출, 
        // Guide.Greeting("안녕하세요. 모듈 가이드 입니다.");
    });
    
    /* namespace 선언 */
    var Annanual_schedule = {
        /* 변수 선언 */
        cnt : 0,
        
        /* 함수 선언 */
        Greeting : function(_msg) {
            alert(_msg);
            return ;
        },
        
        Click : function(_msg) {
            this.cnt++;
            alert(this.cnt + "번 " + _msg);
            
            return ;
        }
    }
    
    

</script>
