package main
import(
	"fmt"
	"time"
)

func ready( w string, sec int){
	time.Sleep( time.Duration(sec) * time.Second )	
	fmt.Println( w, "is ready" )
}

func main(){
	go ready( "2", 2 )
	go ready( "1", 1 )
	fmt.Println( "I'm waiting" )
	time.Sleep( 5 * time.Second )
}
