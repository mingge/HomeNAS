package notifyDb
import(
	"fmt"
	"time"
	"database/sql"
	"os"
	_ "github.com/mattn/go-sqlite3"
	)

func OpenDB( path string ) (db *sql.DB, dbErr error){
	
        db, dbErr = sql.Open( "sqlite3", path )
        if dbErr != nil {
                fmt.Fprintf( os.Stderr, "open database failed: %q\n", path )
                db = nil
                return
        }
        fmt.Fprintf( os.Stderr, "open database : %q\n", path )
        return
}

func CloseDB( db *sql.DB ){
	db.Close()
}

func AddTask( db *sql.DB, actType string, dir string, file string, isDir string )( succ bool){

	succ = false
	tx, err := db.Begin()
	if err != nil{
		return
	} 

	sql  := "REPLACE INTO t_io_notify_tasks (isDir, dir, file, lastUp,type, doing) VALUES ( ?, ?, ?, ?, ?, 0 )"
	stmt, err := tx.Prepare( sql )
	if err != nil{
		return
	}

	defer stmt.Close()

	lastUp := time.Now().Unix()
	_, err  = stmt.Exec( isDir, dir, file, lastUp, actType )
	tx.Commit()
	succ = true
	return
}
