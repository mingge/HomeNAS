package main
import "fmt"
import "os"
import "os/exec"
import "syscall"

func main(){
	fmt.Println( "Welcome to use DiskIONotify go command tool");

	tmpDir := os.TempDir()
	curPath, _:= os.Getwd()

	fmt.Println( "CurPath=", curPath,  " TempDir=", tmpDir )

	// do some clean job
	for i:=0; i<5; i++{
		exec.Command( "/usr/bin/killall -9 inotifywait" )
	}

	fifoPath := tmpDir + "/HomeNAS-diskIoNotify.fifo"
	fmt.Println( "FIFO PIPE path=",  fifoPath )
	os.Remove( fifoPath )

	// make fifo
	err := syscall.Mkfifo( fifoPath, 0777 )
	fmt.Println( err )
//	cmd := exec.Command( "/usr/bin/inotifywait --fromfile $conf --timefmt '%y/%m/%d-%H:%M' --format '%T$$$$$$%e$$$$$$%w$$$$$$%f' -rme attrib,move,modify,close_write,create,delete --outfile " )	
	
}

