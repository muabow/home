#ifndef __ISTRING_CONVERTABLE_H__
#define __ISTRING_CONVERTABLE_H__

#include <string>


class IStringConvertable {
protected:
	IStringConvertable() {};
	virtual ~IStringConvertable() {};

protected:
	virtual void makeAsString(std::string& buf) const = 0;
    
public:
	const char* toString(std::string& buf) const;
};

inline const char* IStringConvertable::toString(std::string& buf) const
{
	makeAsString(buf);
	return buf.c_str();
}

#endif	// __ISTRING_CONVERTABLE_H__