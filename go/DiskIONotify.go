package main
import ("fmt"
	"os"
	"os/exec"
	"syscall"
	"bufio"
	"time"
	"io"
	"bytes"
	"strings"
	"code.google.com/p/go-sqlite/go1/sqlite3"
)

func doCleanJob( fifoPath string ){
	for i:=0; i<2; i++{
		cmd1 := exec.Command( "/usr/bin/killall -9 inotifywait" )
		err1 := cmd1.Run();
		if err1 != nil {
			fmt.Println( err1 )
		}
	}

	os.Remove( fifoPath )
}

func doBeginIoNotify( fifoPath string, confPath string, waitSec int ){
	fmt.Fprintf( os.Stderr, "Waiting for execute IO /usr/bin/inotifywait\n")
	time.Sleep( time.Duration( waitSec ) * time.Second )
	
	fmt.Fprintf( os.Stderr, "Load IO Notify configure file from: %q\n", confPath )

	cmd := exec.Command( "/usr/bin/inotifywait", "--fromfile", confPath, "--timefmt", "%y/%m/%d-%H:%M", "--format", "%T$$$$$$%e$$$$$$%w$$$$$$%f", 
			     "-rme", "attrib,move,modify,close_write,create,delete", "--outfile", fifoPath )	
	verr := cmd.Run()
	if verr != nil {
		fmt.Fprintf( os.Stderr, "=============> %q\n", verr )
	}else{
		fmt.Fprintf( os.Stderr, "run succ\n" )
	}
}

func processEvent( line string ){
	d := strings.Split( line, "$$$$$$" )

	//fmt.Fprintf( os.Stderr, "Idx0=%q, Idx1=%q, Idx2=%q, Idx3=%q\n", d[0], d[1], d[2], d[3] );
	//return

	/*
Idx0="14/03/08-10:04", Idx1="ATTRIB,ISDIR", Idx2="/datas/shares/", Idx3=""
Idx0="14/03/08-10:04", Idx1="ATTRIB,ISDIR", Idx2="/datas/shares/", Idx3=""
Idx0="14/03/08-10:04", Idx1="CLOSE_WRITE,CLOSE", Idx2="/datas/shares/.AppleDouble/", Idx3=".Parent"
Idx0="14/03/08-10:04", Idx1="MODIFY", Idx2="/datas/shares/Network Trash Folder/.AppleDouble/", Idx3=".Parent"
Idx0="14/03/08-10:04", Idx1="CLOSE_WRITE,CLOSE", Idx2="/datas/shares/Network Trash Folder/.AppleDouble/", Idx3=".Parent"
Idx0="14/03/08-10:04", Idx1="MODIFY", Idx2="/datas/shares/Temporary Items/.AppleDouble/", Idx3=".Parent"
Idx0="14/03/08-10:04", Idx1="CLOSE_WRITE,CLOSE", Idx2="/datas/shares/Temporary Items/.AppleDouble/", Idx3=".Parent"
Idx0="14/03/08-10:04", Idx1="MODIFY", Idx2="/datas/shares/.AppleDesktop/", Idx3=".volinfo"
Idx0="14/03/08-10:04", Idx1="MODIFY", Idx2="/datas/shares/.AppleDesktop/", Idx3=".volinfo"
Idx0="14/03/08-10:04", Idx1="CLOSE_WRITE,CLOSE", Idx2="/datas/shares/.AppleDesktop/", Idx3=".volinfo"
	*/
	//fmt.Println( d );
	/*
	for  k, v := range d {
		if strings.Contains( v, ".AppleDouble" ){
			return
		}

		switch k{
			case 0:
			case 1:
			case 2:
		}
		fmt.Println( "k=", k, "  v=", v )
	}
	*/
	eventToDB( d )
}

var dbHandle  *sqlite3.Conn
var dbError   error
func openDB( path string ) (bool){
	dbHandle, dbError = sqlite3.Open( path )
	if dbError != nil {
		fmt.Fprintf( os.Stderr, "open database failed: %q\n", path )
		dbHandle = nil
		return false
	}
	fmt.Fprintf( os.Stderr, "open database : %q\n", path )
	return true
}

func eventToDB(  event [] string ){

	unixTime := time.Now().Unix()

	sql := fmt.Sprintf( "SELECT isDir, dir, file, lastUp, type, doing FROM t_io_notify_tasks WHERE (%v - lastUp) > 5", unixTime);
	fmt.Fprintf( os.Stderr, sql, "\n" )

	
	if event[1] == "HELLO"{

	}
}

func doReadFifo( fifoPath string ){
	// make fifo
	err := syscall.Mkfifo( fifoPath, 0777 )
	if err != nil {
		fmt.Fprintf( os.Stderr, "create named fifo pipe failed: %q" , err )
		return
	}else{
		fmt.Fprintf( os.Stderr, "create fifo pipe succ." )
	}

	file, err := os.OpenFile( fifoPath, os.O_RDONLY, os.ModeNamedPipe )
	if err != nil {
		fmt.Println( err )
	}

	defer file.Close()

	br := bufio.NewReader( file )
	for{
		//line, readErr := br.ReadString( '\n' )
		line, readErr := br.ReadBytes( '\n' )
		if readErr == io.EOF {
			break
		}

		line = bytes.TrimRight( line, "\r\n\t" )
		processEvent( string(line) )
		//fmt.Printf( "===>> %q\n --> %q\n", line, str )
	}
}

func main(){
	fmt.Println( "Welcome to use DiskIONotify go command tool");

	tmpDir := os.TempDir()
	curPath, _:= os.Getwd()

	fmt.Println( "CurPath=", curPath,  " TempDir=", tmpDir )

	fifoPath := tmpDir + "/HomeNAS-diskIoNotify.fifo"
	fmt.Println( "FIFO PIPE path=",  fifoPath )

	doCleanJob( fifoPath )

	confPath := curPath + "/etc/HomeNAS/ionotify.conf"
	dbPath   := curPath + "/../db/BasicData.sqlite"

	openDB( dbPath )

	go doReadFifo( fifoPath )
	go doBeginIoNotify( fifoPath, confPath, 3 )	

	for{
		time.Sleep( time.Duration(1) * time.Second )
	}
}

