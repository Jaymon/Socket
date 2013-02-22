<?php
/**
 *  a socket client (a client will send/receive from a {@link Socket\Server})
 *  
 *  @link http://www.php.net/manual/en/sockets.examples.php
 *  @link http://ca.php.net/manual/en/ref.sockets.php#82163 
 *  
 *  @version 0.1
 *  @author Jay Marcyes
 *  @since 10-14-11
 *  @package Socket 
 ******************************************************************************/
namespace Socket;

class Client extends Server {

  /**
   *  how many ms before timeout
   *  
   *  @var  integer
   */
  protected $timeout_milliseconds = 0;
  
  /**
   *  how many seconds before timeout
   *  
   *  @var  integer
   */
  protected $timeout_seconds = 10;

  /**
   *  create an instance
   *  
   *  @param  string  $host something like localhost or 127.0.0.1 or any other ip
   *  @param  integer $port the port
   */
  public function __construct($host,$port){
  
    $this->create($host,$port);
    $this->connect();
  
  }//method

  /**
   *  writes to the server
   *
   *  @see  http://www.php.net/manual/en/function.socket-write.php   
   *  @param  string  $str  the value to send to the client   
   *  @return integer
   */
  public function write($str){
  
    // Display output back to server
    return (int)socket_write($this->socket,$str);
  
  }//method

  /**
   *  read a response from a Server
   *  
   *  @see  _read()
   *  @return string  the value read from the server
   */
  public function read(){ return $this->_read($this->socket); }//method
  
  /**
   *  disabled on clients, only available for servers
   *     
   *  @see  parent::listen()
   */
  public function listen($callback){ throw new \BadMethodCallException('listen is not supported on Clients'); }//method
  
  /**
   *  connect to a socket for writing/reading
   *
   *  @see  http://www.php.net/manual/en/function.socket-connect.php
   */
  protected function connect(){
  
    // set socket options...
    // http://www.php.net/manual/en/function.socket-connect.php#103492
    // http://www.php.net/manual/en/function.socket-set-option.php#52429
    $timeout = array('sec' => $this->timeout_seconds, 'usec' => $this->timeout_milliseconds);
    
    // set timeouts...
    // timeout on write...
    ///socket_set_option($this->socket,SOL_SOCKET,SO_SNDTIMEO,$timeout);
    // timeout on read...
    socket_set_option($this->socket,SOL_SOCKET,SO_RCVTIMEO,$timeout);
    
    // I can't figure out any other way to test if a socket exists sans E_WARNING without using the @ symbol, 
    // I hate suppressing the warning, but it is all I've been able to find that works
    // Sadly, this is what the official docs recomend also:
    // http://www.php.net/manual/en/sockets.errors.php
    // http://us2.php.net/manual/en/language.operators.errorcontrol.php
    if(!@socket_connect($this->socket,$this->host,$this->port)){
    
      $this->throwError(sprintf('Could not connect to %s:%s',$this->host,$this->port));
    
    }//if
      
    return true;
  
  }//method

}//class
