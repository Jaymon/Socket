<?php
/**
 *  a socket Server (a server will receive/send to a {@link Socket\Client})
 * 
 *  @link http://devzone.zend.com/article/1086  
 *  @link http://www.php.net/manual/en/sockets.examples.php
 *  @link http://ca.php.net/manual/en/ref.sockets.php#82163 
 *  
 *  @todo http://www.php.net/manual/en/function.socket-set-nonblock.php
 *  make the socket non-blocking  
 *  
 *  @version 0.1
 *  @author Jay Marcyes
 *  @since 10-14-11
 *  @package Socket
 ******************************************************************************/
namespace Socket;

class Server {

  /**
   *  the host this server is sending and receiving from
   * 
   *  this is usually an ip address or something like 'localhost'
   *       
   *  @var  string
   */
  protected $host = '';
  
  /**
   *  the port the socket is using
   *
   *  @var  integer
   */
  protected $port = 0;

  /**
   *  hold the socket
   *  
   *  @var  resource
   */
  protected $socket = null;
  
  /**
   *  this is used to listen for Client connections
   *
   *  @var  resource   
   */
  protected $con = null;

  /**
   *  @see  http://www.php.net/manual/en/function.socket-create.php
   *  @var  integer
   */
  protected $domain = AF_INET;
  
  /**
   *  @see  http://www.php.net/manual/en/function.socket-create.php
   *  @var  integer
   */
  protected $type = SOCK_STREAM;
  
  /**
   *  @see  http://www.php.net/manual/en/function.socket-create.php
   *  @var  integer
   */
  protected $protocol = SOL_TCP;
  
  /**
   *  create an instance
   *  
   *  @param  string  $host something like localhost or 127.0.0.1 or any other ip
   *  @param  integer $port the port
   */
  public function __construct($host,$port){
  
    $this->create($host,$port);
    $this->bind();
    
    if(!socket_listen($this->socket)){
    
      $this->throwError('Socket_listen() failed');
    
    }//if
  
  }//method
  
  /**
   *  writes to the last client read from
   *
   *  @see  http://www.php.net/manual/en/function.socket-write.php   
   *  @param  string  $str  the value to send to the client   
   *  @return integer
   */
  public function write($str){
  
    // Display output back to client
    return (int)socket_write($this->con,$str);
  
  }//method
  
  /**
   *  listen for a response and then call $callback
   *
   *  @param  callback  $callback the callback should take a string and return the response   
   */
  public function listen($callback){
  
    if(!is_callable($callback)){
      throw new \InvalidArgumentException('$callback was not valid');
    }//if
    
    $null = chr(0);
    
    // enter a read/respond loop...
    while(true){
    
      $input = $this->read();
      
      $output = call_user_func($callback,$input);
      
      $this->write($output.$null);
    
    }//while
  
  }//method
  
  /**
   *  read from a client
   *  
   *  @see  _read()   
   *  @return string  the value read from client
   */
  public function read(){
  
    $this->con = socket_accept($this->socket);
    return $this->_read($this->con);
    
  }//method
  
  /**
   *  read from a client
   *  
   *  this will basically block until it accepts and reads from a client
   *  
   *  @see  http://www.php.net/manual/en/function.socket-read.php
   *  
   *  @param  resource  $socket the socket to read from      
   *  @return string  the value read from client
   */
  public function _read($socket){
    
    $ret_str = '';
    $chunk_size = 1024;
  
    // Read the input until there is no more...
    do{
    
      $read = socket_read($socket,$chunk_size);
      if($read !== false){
      
        $ret_str .= $read;
        
      }//if/else
    
    }while(($read !== false) && (mb_strlen($read) >= $chunk_size));
    
    return $ret_str;
  
  }//method
  
  /**
   *  create the socket
   *  
   *  @see  http://www.php.net/manual/en/function.socket-create.php
   */
  protected function create($host,$port){
  
    // canary...
    if(empty($host)){ throw new \InvalidArgumentException('$host was empty'); }//if
    if(empty($port)){ throw new \InvalidArgumentException('$port was empty'); }//i
  
    $this->socket = socket_create($this->domain,$this->type,$this->protocol);
    if(empty($this->socket)){ $this->throwError(); }//if
    
    // convert the host to an ip address if it isn't already...
    // http://www.php.net/manual/en/function.socket-connect.php#81332
    // http://www.php.net/manual/en/function.socket-connect.php#84465
    if(!preg_match('#^\d+(\.\d+)+$#',$host)){
    
      $ip = gethostbyname($host);
      if($ip === $host){
      
        $this->throwError(sprintf('Host %s could not be converted to IP address',$host));
      
      }else{
      
        $host = $ip;
      
      }//if/else
      
    }//if
    
    $this->host = $host;
    $this->port = $port;
  
  }//method
  
  /**
   *  @see  http://www.php.net/manual/en/function.socket-bind.php
   */
  protected function bind(){
  
    if(!socket_bind($this->socket,$this->host,$this->port)){
    
      $this->throwError(sprintf('Could not connect to %s:%s because: "%s"',$this->host,$this->port));
    
    }//if
    
    return true;
  
  }//method
  
  /**
   *  throw an UnexpectedValueException with the given $errmsg
   *  
   *  @param  string  $errmsg the error message to add above and beyond the socket error
   *  @param  resource  $socket if not passed in then the default class {@link $socket} will be used
   *  @throws \UnexpectedValueException
   */
  protected function throwError($errmsg = '',$socket = null){
  
    if(!empty($errmsg)){
    
      $errmsg .= '. Socket error: ';
    
    }//if
    
    throw new \UnexpectedValueException(
      sprintf(
        '%s"%s"',
        $errmsg,
        $this->getError($socket)
      )
    );
  
  }//method
  
  /**
   *  get the error message
   *  
   *  @see  http://www.php.net/manual/en/function.socket-strerror.php
   *  @see  http://www.php.net/manual/en/function.socket-last-error.php         
   *  
   *  @param  resource  $socket if not passed in then the default class {@link $socket} will be used
   *  @return string    
   */
  protected function getError($socket = null){
  
    if(empty($socket)){ $socket = $this->socket; }//if
    
    return socket_strerror(socket_last_error($socket));
  
  }//method 
  
  /**
   *  destroy this instance
   *  
   *  basically, close any open sockets
   */
  public function __destruct(){
  
    if(!empty($this->con)){ socket_close($this->con); }//if
  
    socket_close($this->socket);
  
  }//method

}//class
