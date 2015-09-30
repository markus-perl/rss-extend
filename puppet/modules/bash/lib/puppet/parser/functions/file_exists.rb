module Puppet::Parser::Functions
  newfunction(:file_exists, :type => :rvalue) do |args|
    unless args.length == 1
      raise Puppet::Error, "Must provide exactly one arg to file_exists"
    end

    if File.exists?(args[0])
    return 1
    else
    return 0
    end
  end
end